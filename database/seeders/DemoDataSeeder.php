<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\RestaurantStatus;
use App\Enums\SettlementStatus;
use App\Models\Admin;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Cuisine;
use App\Models\DeliveryAssignment;
use App\Models\DeliveryDocument;
use App\Models\DeliveryPartner;
use App\Models\FoodItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PanelNotification;
use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\RestaurantDocument;
use App\Models\RestaurantUser;
use App\Services\DeliveryPartnerDocumentService;
use App\Services\RestaurantDocumentService;
use App\Models\Review;
use App\Models\Settlement;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\CommissionService;
use App\Services\OrderService;
use App\Services\Payment\CodPaymentHandler;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    private const COUNT = 5;

    public function run(): void
    {
        $this->call(CategorySeeder::class);
        $categories = Category::menuOptions();
        $cuisines = $this->seedCuisines();
        $restaurants = $this->seedRestaurants($cuisines);
        $foodItems = $this->seedFoodItems($restaurants, $categories);
        $users = $this->seedUsers();
        $partners = $this->seedDeliveryPartners();
        $this->seedCoupons();
        $this->seedBanners();
        $orders = $this->seedOrders($users, $restaurants, $foodItems, $partners);
        $this->seedReviews($users, $restaurants, $orders);
        $this->seedSupportTickets($users);
        $this->seedRestaurantSupportTickets($restaurants);
        $this->seedNotifications();
        $this->seedSettlements($restaurants);
        $this->seedRestaurantPanelNotifications($restaurants, $orders);

        $this->command?->info('Demo data seeded: '.self::COUNT.' records per section (users, restaurants, partners, menu, orders, reviews, support, marketing, finance).');
    }

    /**
     * @return \Illuminate\Support\Collection<int, Cuisine>
     */
    private function seedCuisines()
    {
        $names = ['Mughlai', 'Italian', 'Thai', 'Mexican', 'Continental'];

        return collect($names)->map(fn (string $name) => Cuisine::query()->updateOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name, 'is_active' => true]
        ));
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Cuisine>  $cuisines
     * @return \Illuminate\Support\Collection<int, Restaurant>
     */
    private function seedRestaurants($cuisines)
    {
        $labels = ['Spice Route', 'Urban Bites', 'Green Bowl', 'Fire Wok', 'Sweet Crust'];

        return collect($labels)->map(function (string $name, int $i) use ($cuisines) {
            $email = $i === 0
                ? 'demo@restaurant.test'
                : 'demo-restaurant-'.($i + 1).'@oniono.test';

            $restaurant = Restaurant::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'owner_name' => 'Owner '.($i + 1),
                    'phone' => '910000000'.($i + 1),
                    'address' => (100 + $i).' Demo Street',
                    'city' => 'Mumbai',
                    'state' => 'MH',
                    'pincode' => '40000'.($i + 1),
                    'status' => RestaurantStatus::Approved,
                    'is_open' => true,
                    'is_featured' => $i < 2,
                ]
            );

            RestaurantUser::query()->updateOrCreate(
                ['email' => $email],
                [
                    'restaurant_id' => $restaurant->id,
                    'name' => $restaurant->owner_name,
                    'password' => Hash::make('password'),
                ]
            );

            $restaurant->cuisines()->syncWithoutDetaching([
                $cuisines[$i % $cuisines->count()]->id,
                $cuisines[($i + 1) % $cuisines->count()]->id,
            ]);

            foreach (app(RestaurantDocumentService::class)->requiredTypes() as $type) {
                RestaurantDocument::query()->updateOrCreate(
                    ['restaurant_id' => $restaurant->id, 'type' => $type],
                    [
                        'path' => 'restaurants/documents/demo-'.$type.'-'.$restaurant->id.'.pdf',
                        'verification_status' => 'verified',
                    ]
                );
            }

            return $restaurant;
        });
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Restaurant>  $restaurants
     * @param  \Illuminate\Support\Collection<int, Category>  $categories
     * @return \Illuminate\Support\Collection<int, FoodItem>
     */
    private function seedFoodItems($restaurants, $categories)
    {
        $dishes = [
            ['Classic Burger', 249, 'non_veg'],
            ['Paneer Tikka Wrap', 199, 'veg'],
            ['Veg Thali', 320, 'veg'],
            ['Chicken Biryani', 280, 'non_veg'],
            ['Chocolate Brownie', 120, 'veg'],
        ];

        return collect($dishes)->map(function (array $dish, int $i) use ($restaurants, $categories) {
            $restaurant = $restaurants[$i % $restaurants->count()];
            $slug = Str::slug($dish[0]).'-'.$restaurant->id;

            return FoodItem::query()->updateOrCreate(
                ['restaurant_id' => $restaurant->id, 'slug' => $slug],
                [
                    'category_id' => $categories[$i % $categories->count()]->id,
                    'name' => $dish[0],
                    'price' => $dish[1],
                    'food_type' => $dish[2],
                    'is_available' => true,
                    'is_featured' => $i < 3,
                ]
            );
        });
    }

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function seedUsers()
    {
        return collect(range(1, self::COUNT))->map(function (int $i) {
            return User::query()->updateOrCreate(
                ['email' => 'demo-customer-'.$i.'@oniono.test'],
                [
                    'name' => 'Demo Customer '.$i,
                    'phone' => '920000000'.$i,
                    'password' => Hash::make('password'),
                    'is_verified' => $i % 2 === 0,
                ]
            );
        });
    }

    /**
     * @return \Illuminate\Support\Collection<int, DeliveryPartner>
     */
    private function seedDeliveryPartners()
    {
        return collect(range(1, self::COUNT))->map(function (int $i) {
            $partner = DeliveryPartner::query()->updateOrCreate(
                ['email' => 'demo-rider-'.$i.'@oniono.test'],
                [
                    'name' => 'Demo Rider '.$i,
                    'phone' => '930000000'.$i,
                    'password' => Hash::make('password'),
                    'vehicle_type' => $i % 2 === 0 ? 'bike' : 'scooter',
                    'vehicle_number' => 'MH-DEMO-'.$i,
                    'status' => $i === 5 ? 'pending' : 'approved',
                    'is_available' => true,
                    'is_active' => true,
                ]
            );

            foreach (app(DeliveryPartnerDocumentService::class)->requiredTypes() as $type) {
                DeliveryDocument::query()->updateOrCreate(
                    ['delivery_partner_id' => $partner->id, 'type' => $type],
                    [
                        'path' => 'delivery/documents/demo-'.$type.'-'.$partner->id.'.pdf',
                        'verification_status' => $i === 5 ? 'pending' : 'verified',
                    ]
                );
            }

            return $partner;
        });
    }

    private function seedCoupons(): void
    {
        $codes = ['WELCOME50', 'FLAT100', 'FREESHIP', 'LUNCH20', 'WEEKEND15'];

        foreach ($codes as $i => $code) {
            Coupon::query()->updateOrCreate(
                ['code' => $code],
                [
                    'title' => 'Demo coupon '.($i + 1),
                    'discount_type' => $i % 2 === 0 ? 'percent' : 'flat',
                    'discount_value' => $i % 2 === 0 ? 15 : 100,
                    'min_order_amount' => 199,
                    'max_discount' => 150,
                    'starts_at' => now()->subDays(7),
                    'expires_at' => now()->addMonths(2),
                    'usage_limit' => 100,
                    'per_user_limit' => 3,
                    'used_count' => $i,
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedBanners(): void
    {
        foreach (range(1, self::COUNT) as $i) {
            Banner::query()->updateOrCreate(
                ['title' => 'Demo Banner '.$i],
                [
                    'image' => 'banners/demo-banner-'.$i.'.jpg',
                    'link' => '/',
                    'sort_order' => $i,
                    'starts_at' => now()->subDay(),
                    'expires_at' => now()->addMonths(1),
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, User>  $users
     * @param  \Illuminate\Support\Collection<int, Restaurant>  $restaurants
     * @param  \Illuminate\Support\Collection<int, FoodItem>  $foodItems
     * @param  \Illuminate\Support\Collection<int, DeliveryPartner>  $partners
     * @return \Illuminate\Support\Collection<int, Order>
     */
    private function seedOrders($users, $restaurants, $foodItems, $partners)
    {
        $statuses = [
            OrderStatus::Pending,
            OrderStatus::Accepted,
            OrderStatus::Preparing,
            OrderStatus::Delivered,
            OrderStatus::Cancelled,
        ];

        return collect(range(1, self::COUNT))->map(function (int $i) use ($users, $restaurants, $foodItems, $partners, $statuses) {
            $user = $users[$i - 1];
            $restaurant = $restaurants[($i - 1) % $restaurants->count()];
            $food = $foodItems[$i - 1];
            $status = $statuses[$i - 1];
            $qty = $i + 1;
            $subtotal = (float) $food->price * $qty;
            $delivery = 40;
            $grand = $subtotal + $delivery;

            $order = Order::query()->updateOrCreate(
                ['order_number' => 'DEMO-ORD-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT)],
                [
                    'user_id' => $user->id,
                    'restaurant_id' => $restaurant->id,
                    'customer_name' => $user->name,
                    'customer_phone' => $user->phone,
                    'status' => $status,
                    'payment_method' => $i % 2 === 0 ? PaymentMethod::Cod : PaymentMethod::Online,
                    'payment_status' => $status === OrderStatus::Delivered ? PaymentStatus::Success : PaymentStatus::Pending,
                    'food_subtotal' => $subtotal,
                    'discount_amount' => 0,
                    'delivery_charge' => $delivery,
                    'grand_total' => $grand,
                    'delivered_at' => $status === OrderStatus::Delivered ? now()->subDays($i) : null,
                    'accepted_at' => in_array($status, [OrderStatus::Accepted, OrderStatus::Preparing, OrderStatus::Delivered], true)
                        ? now()->subDays($i)->addHour()
                        : null,
                    'created_at' => now()->subDays($i),
                    'updated_at' => now()->subDays($i),
                ]
            );

            OrderItem::query()->updateOrCreate(
                ['order_id' => $order->id, 'food_item_id' => $food->id],
                [
                    'item_name' => $food->name,
                    'quantity' => $qty,
                    'unit_price' => $food->price,
                    'total_price' => $subtotal,
                ]
            );

            Payment::query()->updateOrCreate(
                ['order_id' => $order->id],
                [
                    'method' => $order->payment_method,
                    'status' => $order->payment_status,
                    'amount' => $grand,
                    'gateway' => $order->payment_method === PaymentMethod::Online ? 'razorpay' : 'cod',
                ]
            );

            if ($status === OrderStatus::Delivered) {
                $order->update([
                    'commission_percentage_applied' => 10,
                    'commissionable_amount' => $subtotal,
                    'commission_amount' => round($subtotal * 0.10, 2),
                    'restaurant_earning_amount' => round($subtotal * 0.90, 2),
                ]);

                app(CommissionService::class)->recordEarning($order->fresh());

                $partner = $partners->firstWhere('status', 'approved') ?? $partners->first();

                DeliveryAssignment::query()->updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'delivery_partner_id' => $partner->id,
                        'status' => 'completed',
                        'assigned_at' => $order->created_at->copy()->addMinutes(30),
                    ]
                );
            }

            return $order->fresh();
        });
    }

    /**
     * @param  \Illuminate\Support\Collection<int, User>  $users
     * @param  \Illuminate\Support\Collection<int, Restaurant>  $restaurants
     * @param  \Illuminate\Support\Collection<int, Order>  $orders
     */
    private function seedReviews($users, $restaurants, $orders): void
    {
        foreach (range(1, self::COUNT) as $i) {
            $restaurant = $restaurants[($i - 1) % $restaurants->count()];
            $user = $users[$i - 1];
            $order = $orders[($i - 1) % $orders->count()];

            Review::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'restaurant_id' => $restaurant->id,
                    'order_id' => $order->id,
                ],
                [
                    'rating' => 3 + ($i % 3),
                    'comment' => 'Demo review '.$i.': Great food and quick delivery. Sample feedback for admin testing.',
                    'is_visible' => $i !== 5,
                ]
            );
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, User>  $users
     */
    private function seedSupportTickets($users): void
    {
        $subjects = [
            'Order not received',
            'Wrong item delivered',
            'Refund request',
            'App login issue',
            'Restaurant closed but order accepted',
        ];

        foreach ($subjects as $i => $subject) {
            $user = $users[$i];
            $ticket = SupportTicket::query()->updateOrCreate(
                ['ticket_number' => 'DEMO-TKT-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT)],
                [
                    'creator_type' => 'user',
                    'creator_id' => $user->id,
                    'subject' => $subject,
                    'status' => $i === 0 ? 'open' : ($i === 4 ? 'closed' : 'in_progress'),
                    'priority' => $i % 3 === 0 ? 'high' : 'medium',
                ]
            );

            SupportMessage::query()->firstOrCreate(
                [
                    'support_ticket_id' => $ticket->id,
                    'sender_type' => 'user',
                    'sender_id' => $user->id,
                    'message' => 'Demo message from customer about: '.$subject,
                ]
            );
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Restaurant>  $restaurants
     */
    private function seedRestaurantSupportTickets($restaurants): void
    {
        $subjects = [
            'Weekly settlement not received',
            'Need help updating FSSAI document',
            'Menu item showing wrong price',
            'How to pause orders during maintenance',
            'Commission rate clarification',
        ];

        foreach ($subjects as $i => $subject) {
            $restaurant = $restaurants[$i % $restaurants->count()];
            $restaurantUser = RestaurantUser::query()->where('restaurant_id', $restaurant->id)->first();

            if (! $restaurantUser) {
                continue;
            }

            $ticket = SupportTicket::query()->updateOrCreate(
                ['ticket_number' => 'DEMO-RST-TKT-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT)],
                [
                    'creator_type' => 'restaurant_user',
                    'creator_id' => $restaurantUser->id,
                    'subject' => $subject,
                    'status' => $i === 0 ? 'open' : ($i === 4 ? 'closed' : 'in_progress'),
                    'priority' => $i % 3 === 0 ? 'high' : 'medium',
                ]
            );

            SupportMessage::query()->updateOrCreate(
                [
                    'support_ticket_id' => $ticket->id,
                    'sender_type' => 'restaurant_user',
                    'sender_id' => $restaurantUser->id,
                ],
                ['message' => 'Demo ticket from '.$restaurant->name.': '.$subject]
            );

            if ($i > 0) {
                SupportMessage::query()->updateOrCreate(
                    [
                        'support_ticket_id' => $ticket->id,
                        'sender_type' => 'admin',
                        'sender_id' => Admin::query()->value('id') ?? 1,
                    ],
                    ['message' => 'Thanks for reaching out. Our partner support team is reviewing this request.']
                );
            }
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Restaurant>  $restaurants
     * @param  \Illuminate\Support\Collection<int, Order>  $orders
     */
    private function seedRestaurantPanelNotifications($restaurants, $orders): void
    {
        foreach ($restaurants as $restaurant) {
            $pending = $orders->first(fn (Order $o) => $o->restaurant_id === $restaurant->id && $o->status === OrderStatus::Pending);
            if ($pending) {
                PanelNotification::query()->updateOrCreate(
                    [
                        'notifiable_type' => 'restaurant',
                        'notifiable_id' => $restaurant->id,
                        'type' => 'order_new',
                        'title' => 'New order #'.$pending->order_number,
                    ],
                    [
                        'body' => 'A customer placed a new order. Accept or reject from Orders.',
                        'data' => [
                            'order_id' => $pending->id,
                            'order_number' => $pending->order_number,
                            'url' => url('/restaurant/orders/'.$pending->id),
                        ],
                        'is_read' => false,
                    ]
                );
            }

            $cancelled = $orders->first(fn (Order $o) => $o->restaurant_id === $restaurant->id && $o->status === OrderStatus::Cancelled);
            if ($cancelled) {
                PanelNotification::query()->updateOrCreate(
                    [
                        'notifiable_type' => 'restaurant',
                        'notifiable_id' => $restaurant->id,
                        'type' => 'order_cancelled',
                        'title' => 'Order #'.$cancelled->order_number.' cancelled',
                    ],
                    [
                        'body' => 'Demo cancellation notice for testing.',
                        'data' => [
                            'order_id' => $cancelled->id,
                            'url' => url('/restaurant/orders/'.$cancelled->id),
                        ],
                        'is_read' => true,
                    ]
                );
            }

            $settlement = Settlement::query()->where('restaurant_id', $restaurant->id)->latest()->first();
            if ($settlement) {
                PanelNotification::query()->updateOrCreate(
                    [
                        'notifiable_type' => 'restaurant',
                        'notifiable_id' => $restaurant->id,
                        'type' => 'settlement',
                        'title' => 'Settlement '.$settlement->settlement_number,
                    ],
                    [
                        'body' => 'Payout processed — demo settlement notification.',
                        'data' => [
                            'settlement_id' => $settlement->id,
                            'url' => url('/restaurant/settlements/'.$settlement->id),
                        ],
                        'is_read' => false,
                    ]
                );
            }
        }

        PanelNotification::query()->updateOrCreate(
            [
                'notifiable_type' => 'restaurant',
                'notifiable_id' => $restaurants->first()->id,
                'type' => 'announcement',
                'title' => 'Welcome to Oniono partner updates',
            ],
            [
                'body' => 'Platform announcements and promotional offers will appear here.',
                'data' => ['url' => url('/restaurant/notifications')],
                'is_read' => false,
            ]
        );
    }

    private function seedNotifications(): void
    {
        $admin = Admin::query()->first();

        if (! $admin) {
            return;
        }

        foreach (range(1, self::COUNT) as $i) {
            PanelNotification::query()->updateOrCreate(
                [
                    'notifiable_type' => 'admin',
                    'notifiable_id' => $admin->id,
                    'title' => 'Demo notification '.$i,
                ],
                [
                    'body' => 'Sample admin alert #'.$i.' for testing the messages panel.',
                    'type' => $i % 2 === 0 ? 'info' : 'warning',
                    'is_read' => $i > 3,
                    'data' => ['demo' => true],
                ]
            );
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Restaurant>  $restaurants
     */
    private function seedSettlements($restaurants): void
    {
        $admin = Admin::query()->first();

        foreach (range(1, self::COUNT) as $i) {
            $restaurant = $restaurants[($i - 1) % $restaurants->count()];

            Settlement::query()->updateOrCreate(
                ['settlement_number' => 'DEMO-SET-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT)],
                [
                    'restaurant_id' => $restaurant->id,
                    'processed_by_admin_id' => $admin?->id,
                    'amount' => 1500 + ($i * 250),
                    'payment_mode' => $i % 2 === 0 ? 'bank_transfer' : 'upi',
                    'transaction_reference' => 'TXN-DEMO-'.$i,
                    'payment_date' => now()->subDays($i)->toDateString(),
                    'admin_remarks' => 'Demo settlement batch '.$i,
                    'status' => match ($i % 4) {
                        0 => SettlementStatus::Paid,
                        1 => SettlementStatus::Pending,
                        2 => SettlementStatus::Processing,
                        default => SettlementStatus::OnHold,
                    },
                ]
            );
        }
    }
}
