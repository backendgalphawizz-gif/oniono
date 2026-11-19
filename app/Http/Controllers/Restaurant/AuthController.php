<?php

namespace App\Http\Controllers\Restaurant;

use App\Enums\RestaurantStatus;
use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\RestaurantImage;
use App\Models\RestaurantUser;
use App\Services\RestaurantAuthConfig;
use App\Services\RestaurantDocumentService;
use App\Services\RestaurantRegistrationDraft;
use App\Services\RestaurantRegistrationOtpService;
use App\Support\PhoneNumber;
use App\Models\City;
use App\Models\State;
use App\Support\LocationNames;
use App\Support\RestaurantRegistrationRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        protected RestaurantAuthConfig $authConfig,
        protected RestaurantRegistrationOtpService $registrationOtp,
        protected RestaurantRegistrationDraft $registrationDraft,
    ) {}

    public function showRegister(): View
    {
        $draft = $this->registrationDraft->get() ?? [];
        $phone = is_string($draft['phone'] ?? null) ? $draft['phone'] : '';

        [$selectedStateId, $selectedCityId, $cities] = $this->registrationLocationSelection($draft);

        return view('restaurant.auth.register-step1', [
            'authConfig' => $this->authConfig,
            'draft' => $draft,
            'phoneVerified' => $phone !== '' && $this->registrationOtp->isPhoneVerifiedInSession($phone),
            'states' => State::query()->orderBy('name')->get(['id', 'name']),
            'cities' => $cities,
            'selectedStateId' => $selectedStateId,
            'selectedCityId' => $selectedCityId,
        ]);
    }

    public function saveRegistrationStep1(Request $request): RedirectResponse
    {
        $existingDraft = $this->registrationDraft->get() ?? [];
        $rules = RestaurantRegistrationRules::stepOneRules();
        if ($existingDraft !== []) {
            $rules['password'] = ['nullable', 'string', 'min:8', 'confirmed'];
        }
        $rules['gallery_images'] = ['nullable', 'array', 'max:'.$this->authConfig->galleryMaxImages()];
        $rules['gallery_images.*'] = ['image', 'max:'.$this->authConfig->galleryMaxKb()];

        $data = $request->validate($rules);
        $data = array_merge($data, LocationNames::resolveFromIds($data));

        if (empty($data['password'] ?? null)) {
            if (empty($existingDraft['password'])) {
                throw ValidationException::withMessages(['password' => 'Enter a password to continue.']);
            }
            $data['password'] = $existingDraft['password'];
        }

        $normalizedPhone = PhoneNumber::normalize($data['phone']);
        if (Restaurant::query()->where('phone', $normalizedPhone)->exists()) {
            throw ValidationException::withMessages(['phone' => 'This mobile number is already registered.']);
        }

        $this->assertRegistrationPhoneVerified($normalizedPhone);

        $data['phone'] = $normalizedPhone;

        unset($data['logo'], $data['gallery_images']);

        $existing = $this->registrationDraft->get() ?? [];
        $draft = array_merge($existing, $data);
        $draft = $this->registrationDraft->storeStepOneFiles($request, $draft);

        $this->registrationDraft->put($draft);

        return redirect()->route('restaurant.register.documents');
    }

    public function showRegisterDocuments(): RedirectResponse|View
    {
        if (! $this->registrationDraft->exists()) {
            return redirect()
                ->route('restaurant.register')
                ->withErrors(['register' => 'Complete step 1 before uploading documents.']);
        }

        return view('restaurant.auth.register-step2', [
            'authConfig' => $this->authConfig,
            'draft' => $this->registrationDraft->get() ?? [],
        ]);
    }

    public function restartRegistration(): RedirectResponse
    {
        $this->registrationDraft->clear();
        $this->registrationOtp->clearVerifiedSession();

        return redirect()->route('restaurant.register')->with('success', 'Registration started over.');
    }

    public function register(Request $request): RedirectResponse
    {
        $draft = $this->registrationDraft->get();
        if ($draft === null) {
            return redirect()
                ->route('restaurant.register')
                ->withErrors(['register' => 'Your session expired. Please start registration again.']);
        }

        $documentService = app(RestaurantDocumentService::class);
        $rules = array_merge(
            RestaurantRegistrationRules::payoutRules(),
            $documentService->registrationValidationRules(),
            $documentService->optionalRegistrationValidationRules(),
        );

        $payout = $request->validate($rules);
        $payout['bank_ifsc'] = strtoupper(trim($payout['bank_ifsc']));
        $payout['bank_account_number'] = preg_replace('/\s+/', '', $payout['bank_account_number']);
        $draft = array_merge($draft, collect($payout)->only([
            'bank_account_name', 'bank_account_number', 'bank_ifsc', 'bank_name', 'gst_number',
        ])->toArray());

        $normalizedPhone = $draft['phone'] ?? '';
        if ($normalizedPhone === '' || Restaurant::query()->where('phone', $normalizedPhone)->exists()) {
            $this->registrationDraft->clear();

            throw ValidationException::withMessages(['phone' => 'This mobile number is already registered or invalid. Start again.']);
        }

        $this->assertRegistrationPhoneVerified($normalizedPhone);

        DB::transaction(function () use ($draft, $request, $normalizedPhone, $documentService) {
            $restaurantData = collect($draft)->only([
                'name', 'owner_name', 'email', 'address', 'city', 'state', 'pincode', 'description',
                'bank_account_name', 'bank_account_number', 'bank_ifsc', 'bank_name', 'gst_number',
            ])->toArray();

            $restaurantData['phone'] = $normalizedPhone;
            $restaurantData['phone_verified_at'] = now();
            $restaurantData['status'] = RestaurantStatus::Pending;

            if (! empty($draft['logo_path'])) {
                $restaurantData['logo'] = $this->promoteTempFile($draft['logo_path'], 'restaurants/logos');
            }

            $restaurant = Restaurant::query()->create($restaurantData);

            RestaurantUser::query()->create([
                'restaurant_id' => $restaurant->id,
                'name' => $draft['owner_name'],
                'email' => $draft['email'],
                'password' => Hash::make($draft['password']),
            ]);

            foreach ($draft['gallery_paths'] ?? [] as $index => $tempPath) {
                $path = $this->promoteTempFile($tempPath, 'restaurants/gallery');
                RestaurantImage::query()->create([
                    'restaurant_id' => $restaurant->id,
                    'path' => $path,
                    'sort_order' => $index,
                ]);
            }

            $documentService->storeFromRequest($request, $restaurant, false);
        });

        $this->registrationDraft->clear();
        $this->registrationOtp->clearVerifiedSession();

        return redirect()->route('restaurant.login')->with('success', 'Registration submitted. Await admin approval.');
    }

    public function sendRegistrationOtp(Request $request): JsonResponse
    {
        $request->validate(RestaurantRegistrationRules::otpPhoneRules());

        $normalizedPhone = PhoneNumber::normalize($request->input('phone'));
        if (Restaurant::query()->where('phone', $normalizedPhone)->exists()) {
            return response()->json(['ok' => false, 'message' => 'This mobile number is already registered.'], 422);
        }

        $result = $this->registrationOtp->send($request->input('phone'));

        return response()->json($result, $result['ok'] ? 200 : 422);
    }

    public function verifyRegistrationOtp(Request $request): JsonResponse
    {
        $request->validate([
            ...RestaurantRegistrationRules::otpPhoneRules(),
            'otp' => ['required', 'string', 'size:'.$this->authConfig->otpLength()],
        ]);

        $normalizedPhone = PhoneNumber::normalize($request->input('phone'));
        if (Restaurant::query()->where('phone', $normalizedPhone)->exists()) {
            return response()->json(['ok' => false, 'message' => 'This mobile number is already registered.'], 422);
        }

        if (! $this->registrationOtp->verify($request->input('phone'), $request->input('otp'))) {
            return response()->json(['ok' => false, 'message' => 'Invalid or expired OTP.'], 422);
        }

        return response()->json(['ok' => true, 'message' => 'Mobile number verified.']);
    }

    public function showLogin(): View
    {
        $methods = $this->authConfig->enabledLoginMethods();
        if ($methods === []) {
            abort(503, 'Restaurant login is not configured.');
        }

        return view('restaurant.auth.login', [
            'authConfig' => $this->authConfig,
            'loginMethods' => $methods,
            'defaultLoginMethod' => $this->authConfig->defaultLoginMethod(),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $methods = $this->authConfig->enabledLoginMethods();
        if ($methods === []) {
            abort(503, 'Restaurant login is not configured.');
        }

        $data = $request->validate([
            'login_method' => ['required', Rule::in($methods)],
            'identifier' => ['required', 'string', 'max:255'],
            'password' => ['required'],
        ]);

        $user = match ($data['login_method']) {
            'phone' => $this->findUserByPhone($data['identifier']),
            default => RestaurantUser::query()->where('email', $data['identifier'])->first(),
        };

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return back()
                ->withErrors(['identifier' => 'Invalid credentials.'])
                ->onlyInput('identifier', 'login_method');
        }

        if (! $user->is_active) {
            return back()
                ->withErrors(['identifier' => 'Restaurant account is inactive.'])
                ->onlyInput('identifier', 'login_method');
        }

        Auth::guard('restaurant')->login($user, $request->boolean('remember'));

        $restaurant = $user->restaurant;

        if ($restaurant->status !== RestaurantStatus::Approved) {
            Auth::guard('restaurant')->logout();

            return back()->withErrors([
                'identifier' => match ($restaurant->status) {
                    RestaurantStatus::Pending => 'Registration pending approval.',
                    RestaurantStatus::Rejected => 'Registration rejected: '.($restaurant->rejection_reason ?? 'Contact support.'),
                    RestaurantStatus::Suspended => 'Account suspended.',
                },
            ])->onlyInput('identifier', 'login_method');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('restaurant.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('restaurant')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('restaurant.login');
    }

    private function findUserByPhone(string $identifier): ?RestaurantUser
    {
        $normalized = PhoneNumber::normalize($identifier);
        if ($normalized === '') {
            return null;
        }

        $restaurant = Restaurant::query()->where('phone', $normalized)->first();

        return $restaurant?->user;
    }

    private function assertRegistrationPhoneVerified(string $normalizedPhone): void
    {
        if (! $this->registrationOtp->isPhoneVerifiedInSession($normalizedPhone)) {
            throw ValidationException::withMessages([
                'phone' => 'Verify your mobile number with OTP before continuing.',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $draft
     * @return array{0: int|null, 1: int|null, 2: \Illuminate\Support\Collection<int, City>}
     */
    private function registrationLocationSelection(array $draft): array
    {
        $selectedStateId = old('state_id', $draft['state_id'] ?? null);
        $selectedCityId = old('city_id', $draft['city_id'] ?? null);

        if (! $selectedStateId && ! empty($draft['state'])) {
            $selectedStateId = State::query()->where('name', $draft['state'])->value('id');
        }

        if (! $selectedCityId && $selectedStateId && ! empty($draft['city'])) {
            $selectedCityId = City::query()
                ->where('state_id', $selectedStateId)
                ->where('name', $draft['city'])
                ->value('id');
        }

        $cities = $selectedStateId
            ? City::query()->where('state_id', $selectedStateId)->orderBy('name')->get(['id', 'name'])
            : collect();

        return [$selectedStateId, $selectedCityId, $cities];
    }

    private function promoteTempFile(string $tempPath, string $targetDirectory): string
    {
        $filename = basename($tempPath);
        $destination = rtrim($targetDirectory, '/').'/'.$filename;

        if (Storage::disk('public')->exists($tempPath)) {
            Storage::disk('public')->move($tempPath, $destination);

            return $destination;
        }

        return $tempPath;
    }
}
