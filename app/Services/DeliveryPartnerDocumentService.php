<?php

namespace App\Services;

use App\Models\DeliveryDocument;
use App\Models\DeliveryPartner;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DeliveryPartnerDocumentService
{
    /**
     * @return array<string, array{label: string, description: string, max_kb: int, mimes: string}>
     */
    public function requiredDefinitions(): array
    {
        return config('delivery_partner_documents.required', []);
    }

    /** @return list<string> */
    public function requiredTypes(): array
    {
        return array_keys($this->requiredDefinitions());
    }

    public function label(string $type): string
    {
        return $this->requiredDefinitions()[$type]['label'] ?? ucwords(str_replace('_', ' ', $type));
    }

    public function shortLabel(string $type): string
    {
        $meta = $this->requiredDefinitions()[$type] ?? null;

        return $meta['short_label'] ?? $meta['label'] ?? ucwords(str_replace('_', ' ', $type));
    }

    /** @return list<string> */
    public function primaryDocumentTypes(): array
    {
        return ['photo_id', 'driving_license', 'vehicle_rc'];
    }

    /** @return list<string> */
    private function legacyTypesFor(string $type): array
    {
        return match ($type) {
            'photo_id' => ['id_proof', 'aadhaar'],
            'driving_license' => ['license', 'dl'],
            'vehicle_rc' => ['rc', 'vehicle_registration'],
            default => [],
        };
    }

    public function documentForType(DeliveryPartner $partner, string $type): ?DeliveryDocument
    {
        $partner->loadMissing('documents');
        $byType = $partner->documents->keyBy('type');

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
    public function requiredValidationRules(): array
    {
        $rules = [];
        foreach ($this->requiredDefinitions() as $type => $meta) {
            $rules["documents.{$type}"] = [
                'required',
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
    public function optionalUpdateValidationRules(): array
    {
        $rules = [];
        foreach ($this->requiredDefinitions() as $type => $meta) {
            $rules["documents.{$type}"] = [
                'nullable',
                'file',
                'max:'.$meta['max_kb'],
                'mimes:'.$meta['mimes'],
            ];
        }

        return $rules;
    }

    public function storeFromRequest(Request $request, DeliveryPartner $partner, bool $requireAll = false): void
    {
        foreach ($this->requiredTypes() as $type) {
            if ($request->hasFile("documents.{$type}")) {
                $this->storeDocument($partner, $type, $request->file("documents.{$type}"));
            } elseif ($requireAll) {
                continue;
            }
        }
    }

    public function storeDocument(DeliveryPartner $partner, string $type, UploadedFile $file): DeliveryDocument
    {
        if (! array_key_exists($type, $this->requiredDefinitions())) {
            throw new \InvalidArgumentException("Unknown document type: {$type}");
        }

        $existing = DeliveryDocument::query()
            ->where('delivery_partner_id', $partner->id)
            ->where('type', $type)
            ->first();

        if ($existing && $existing->path) {
            Storage::disk('public')->delete($existing->path);
        }

        $path = $file->store('delivery/documents', 'public');

        return DeliveryDocument::query()->updateOrCreate(
            ['delivery_partner_id' => $partner->id, 'type' => $type],
            [
                'path' => $path,
                'verification_status' => 'pending',
                'admin_note' => null,
            ]
        );
    }

    /**
     * @return array<int, array{type: string, label: string, description: string, uploaded: bool, status: ?string, document: ?DeliveryDocument}>
     */
    public function checklist(DeliveryPartner $partner): array
    {
        return collect($this->requiredDefinitions())->map(function (array $meta, string $type) use ($partner) {
            $doc = $this->documentForType($partner, $type);

            return [
                'type' => $type,
                'label' => $meta['label'],
                'short_label' => $meta['short_label'] ?? $meta['label'],
                'description' => $meta['description'],
                'uploaded' => $doc !== null,
                'status' => $doc?->verification_status,
                'document' => $doc,
            ];
        })->values()->all();
    }

    public function hasAllRequiredUploaded(DeliveryPartner $partner): bool
    {
        foreach ($this->requiredTypes() as $type) {
            if ($this->documentForType($partner, $type) === null) {
                return false;
            }
        }

        return true;
    }

    public function hasAllRequiredVerified(DeliveryPartner $partner): bool
    {
        if (! $this->hasAllRequiredUploaded($partner)) {
            return false;
        }

        foreach ($this->requiredTypes() as $type) {
            $doc = $this->documentForType($partner, $type);
            if ($doc && $doc->verification_status !== 'verified') {
                return false;
            }
        }

        return true;
    }

    public function missingRequiredLabels(DeliveryPartner $partner): array
    {
        return collect($this->requiredTypes())
            ->filter(fn (string $type) => $this->documentForType($partner, $type) === null)
            ->map(fn (string $type) => $this->label($type))
            ->values()
            ->all();
    }
}
