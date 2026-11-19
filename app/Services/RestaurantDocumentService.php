<?php

namespace App\Services;

use App\Models\Restaurant;
use App\Models\RestaurantDocument;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class RestaurantDocumentService
{
    /**
     * @return array<string, array{label: string, description: string, max_kb: int, mimes: string, required_for_registration?: bool, required_for_approval?: bool}>
     */
    public function documentDefinitions(): array
    {
        $documents = config('restaurant_documents.documents');
        if (is_array($documents) && $documents !== []) {
            return $documents;
        }

        return config('restaurant_documents.required', []);
    }

    /**
     * @return array<string, array{label: string, description: string, max_kb: int, mimes: string}>
     */
    public function requiredDefinitions(): array
    {
        return $this->documentDefinitions();
    }

    /** @return list<string> */
    public function registrationRequiredTypes(): array
    {
        return $this->typesMatchingFlag('required_for_registration');
    }

    /** @return list<string> */
    public function approvalRequiredTypes(): array
    {
        return $this->typesMatchingFlag('required_for_approval');
    }

    /** @return list<string> */
    private function typesMatchingFlag(string $flag): array
    {
        $definitions = $this->documentDefinitions();
        $usesExplicitFlags = collect($definitions)->contains(fn (array $meta) => array_key_exists($flag, $meta));

        return collect($definitions)
            ->filter(function (array $meta) use ($flag, $usesExplicitFlags) {
                if ($usesExplicitFlags) {
                    return (bool) ($meta[$flag] ?? false);
                }

                return true;
            })
            ->keys()
            ->values()
            ->all();
    }

    /** @return list<string> */
    public function requiredTypes(): array
    {
        return $this->approvalRequiredTypes();
    }

    public function isRequiredForRegistration(string $type): bool
    {
        $meta = $this->documentDefinitions()[$type] ?? null;

        return $meta !== null && (bool) ($meta['required_for_registration'] ?? false);
    }

    public function label(string $type): string
    {
        return $this->documentDefinitions()[$type]['label'] ?? ucwords(str_replace('_', ' ', $type));
    }

    public function shortLabel(string $type): string
    {
        $meta = $this->documentDefinitions()[$type] ?? null;

        return $meta['short_label'] ?? $meta['label'] ?? ucwords(str_replace('_', ' ', $type));
    }

    /** @return list<string> */
    private function legacyTypesFor(string $type): array
    {
        return match ($type) {
            'fssai_license' => ['fssai', 'food_license'],
            'shop_establishment' => ['business_license', 'trade_license'],
            'owner_id_proof' => ['id_proof', 'aadhaar'],
            default => [],
        };
    }

    public function documentForType(Restaurant $restaurant, string $type): ?RestaurantDocument
    {
        $restaurant->loadMissing('documents');
        $byType = $restaurant->documents->keyBy('type');

        if ($doc = $byType->get($type)) {
            return $doc;
        }

        foreach ($this->legacyTypesFor($type) as $legacy) {
            if ($doc = $byType->get($legacy)) {
                return $doc;
            }
        }

        return null;
    }

    public function isImagePath(?string $path): bool
    {
        if (! $path) {
            return false;
        }

        return (bool) preg_match('/\.(jpe?g|png|webp|gif)$/i', $path);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function registrationValidationRules(): array
    {
        return $this->documentFileRules(requiredOnly: true);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function optionalRegistrationValidationRules(): array
    {
        return $this->documentFileRules(requiredOnly: false);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function optionalUpdateValidationRules(): array
    {
        $rules = [];
        foreach ($this->documentDefinitions() as $type => $meta) {
            $rules["documents.{$type}"] = [
                'nullable',
                'file',
                'max:'.$meta['max_kb'],
                'mimes:'.$meta['mimes'],
            ];
        }

        return $rules;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function documentFileRules(bool $requiredOnly): array
    {
        $rules = [];
        foreach ($this->documentDefinitions() as $type => $meta) {
            $isRequired = (bool) ($meta['required_for_registration'] ?? false);
            if ($requiredOnly && ! $isRequired) {
                continue;
            }
            if (! $requiredOnly && $isRequired) {
                continue;
            }

            $fieldRules = [
                $isRequired ? 'required' : 'nullable',
                'file',
                'max:'.$meta['max_kb'],
                'mimes:'.$meta['mimes'],
            ];

            $rules["documents.{$type}"] = $fieldRules;
        }

        return $rules;
    }

    public function storeFromRequest(Request $request, Restaurant $restaurant, bool $requireAll = false): void
    {
        foreach (array_keys($this->documentDefinitions()) as $type) {
            if ($request->hasFile("documents.{$type}")) {
                $this->storeDocument($restaurant, $type, $request->file("documents.{$type}"));
            } elseif ($requireAll && in_array($type, $this->registrationRequiredTypes(), true)) {
                continue;
            }
        }
    }

    public function storeDocument(Restaurant $restaurant, string $type, UploadedFile $file): RestaurantDocument
    {
        if (! array_key_exists($type, $this->documentDefinitions())) {
            throw new \InvalidArgumentException("Unknown document type: {$type}");
        }

        $existing = RestaurantDocument::query()
            ->where('restaurant_id', $restaurant->id)
            ->where('type', $type)
            ->first();

        if ($existing && $existing->path) {
            Storage::disk('public')->delete($existing->path);
        }

        $path = $file->store('restaurants/documents', 'public');

        return RestaurantDocument::query()->updateOrCreate(
            ['restaurant_id' => $restaurant->id, 'type' => $type],
            [
                'path' => $path,
                'verification_status' => 'pending',
                'admin_note' => null,
            ]
        );
    }

    /**
     * @return array<int, array{type: string, label: string, description: string, uploaded: bool, status: ?string, document: ?RestaurantDocument}>
     */
    public function checklist(Restaurant $restaurant): array
    {
        return collect($this->documentDefinitions())->map(function (array $meta, string $type) use ($restaurant) {
            $doc = $this->documentForType($restaurant, $type);

            return [
                'type' => $type,
                'label' => $meta['label'],
                'short_label' => $meta['short_label'] ?? $meta['label'],
                'description' => $meta['description'],
                'required_for_approval' => (bool) ($meta['required_for_approval'] ?? false),
                'uploaded' => $doc !== null,
                'status' => $doc?->verification_status,
                'document' => $doc,
            ];
        })->values()->all();
    }

    public function hasAllRequiredUploaded(Restaurant $restaurant): bool
    {
        foreach ($this->approvalRequiredTypes() as $type) {
            if ($this->documentForType($restaurant, $type) === null) {
                return false;
            }
        }

        return true;
    }

    public function hasAllRequiredVerified(Restaurant $restaurant): bool
    {
        if (! $this->hasAllRequiredUploaded($restaurant)) {
            return false;
        }

        foreach ($this->approvalRequiredTypes() as $type) {
            $doc = $this->documentForType($restaurant, $type);
            if ($doc && $doc->verification_status !== 'verified') {
                return false;
            }
        }

        return true;
    }

    public function missingRequiredLabels(Restaurant $restaurant): array
    {
        return collect($this->approvalRequiredTypes())
            ->filter(fn (string $type) => $this->documentForType($restaurant, $type) === null)
            ->map(fn (string $type) => $this->label($type))
            ->values()
            ->all();
    }
}
