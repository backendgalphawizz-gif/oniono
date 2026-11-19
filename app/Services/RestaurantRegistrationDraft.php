<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RestaurantRegistrationDraft
{
    public const SESSION_KEY = 'restaurant_registration_draft';

    public const SESSION_ID_KEY = 'restaurant_registration_draft_id';

    public const SESSION_STARTED_AT = 'restaurant_registration_draft_at';

    public const TTL_SECONDS = 3600;

    public function id(): string
    {
        $id = session(self::SESSION_ID_KEY);
        if (! is_string($id) || $id === '') {
            $id = (string) str()->uuid();
            session([self::SESSION_ID_KEY => $id]);
        }

        return $id;
    }

    public function exists(): bool
    {
        return is_array(session(self::SESSION_KEY)) && $this->isFresh();
    }

    public function get(): ?array
    {
        if (! $this->exists()) {
            return null;
        }

        return session(self::SESSION_KEY);
    }

    public function put(array $data): void
    {
        session([
            self::SESSION_KEY => $data,
            self::SESSION_STARTED_AT => now()->timestamp,
        ]);
    }

    public function clear(): void
    {
        $draft = session(self::SESSION_KEY);
        if (is_array($draft)) {
            $this->deleteTempFiles($draft);
        }

        $draftId = session(self::SESSION_ID_KEY);
        if (is_string($draftId) && $draftId !== '') {
            Storage::disk('public')->deleteDirectory('restaurants/tmp/'.$draftId);
        }

        session()->forget([
            self::SESSION_KEY,
            self::SESSION_ID_KEY,
            self::SESSION_STARTED_AT,
        ]);
    }

    public function storeStepOneFiles(Request $request, array $draft): array
    {
        $base = 'restaurants/tmp/'.$this->id();

        if ($request->hasFile('logo')) {
            if (! empty($draft['logo_path'])) {
                Storage::disk('public')->delete($draft['logo_path']);
            }
            $draft['logo_path'] = $request->file('logo')->store($base.'/logo', 'public');
        }

        if ($request->hasFile('gallery_images')) {
            foreach ($draft['gallery_paths'] ?? [] as $path) {
                Storage::disk('public')->delete($path);
            }
            $draft['gallery_paths'] = [];
            foreach ($request->file('gallery_images') as $file) {
                $draft['gallery_paths'][] = $file->store($base.'/gallery', 'public');
            }
        }

        return $draft;
    }

    /**
     * @param  list<string>  $galleryPaths
     */
    private function deleteTempFiles(array $draft): void
    {
        if (! empty($draft['logo_path'])) {
            Storage::disk('public')->delete($draft['logo_path']);
        }
        foreach ($draft['gallery_paths'] ?? [] as $path) {
            Storage::disk('public')->delete($path);
        }
    }

    private function isFresh(): bool
    {
        $started = (int) session(self::SESSION_STARTED_AT, 0);
        if ($started === 0) {
            return false;
        }

        return (time() - $started) <= self::TTL_SECONDS;
    }
}
