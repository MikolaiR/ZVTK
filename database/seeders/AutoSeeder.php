<?php

namespace Database\Seeders;

use App\Enums\Statuses;
use App\Models\Auto;
use App\Models\AutoBrand;
use App\Models\AutoLocationPeriod;
use App\Models\AutoModel;
use App\Models\AutoSale;
use App\Models\Color;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Parking;
use App\Models\Provider;
use App\Models\Sender;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class AutoSeeder extends Seeder
{
    use WithoutModelEvents;
    public function run(): void
    {
        $this->ensureMinimumReferences();
        $media = $this->ensureSampleMediaFiles();

        $users = User::query()->get();
        $providers = Provider::query()->get();
        $senders = Sender::query()->get();
        $parkings = Parking::query()->get();
        $customers = Customer::query()->get();
        $colors = Color::query()->get();

        $usedVins = [];

        Auto::withoutEvents(function () use ($users, $providers, $senders, $parkings, $customers, $colors, &$usedVins, $media) {
            for ($i = 0; $i < 50; $i++) {
                $autoModel = AutoModel::query()->inRandomOrder()->first();
                $brandName = optional($autoModel->brand)->name ?? 'Unknown';

                $provider = $providers->random();
                $sender = $senders->random();
                $companyId = $provider->company_id ?? Company::query()->inRandomOrder()->value('id');
                $colorId = $colors->random()->id;
                do {
                    $vin = $this->generateVin();
                } while (in_array($vin, $usedVins, true) || Auto::query()->where('vin', $vin)->exists());
                $usedVins[] = $vin;

                $yearDate = Carbon::create(rand(2008, (int) now()->format('Y') - 1), 1, 1);

                $finalStatus = Arr::random([
                    Statuses::Delivery,
                    Statuses::DeliveryToParking,
                    Statuses::Parking,
                    Statuses::Customer,
                    Statuses::Sale,
                ]);

                $autoTitle = trim($brandName . ' ' . ($autoModel->name ?? 'Model') . ' ' . $vin);

                $auto = Auto::query()->create([
                    'title' => $autoTitle,
                    'departure_date' => now()->subDays(rand(10, 60))->startOfDay(),
                    'auto_model_id' => $autoModel->id,
                    'color_id' => $colorId,
                    'company_id' => $companyId,
                    'sender_id' => $sender->id,
                    'provider_id' => $provider->id,
                    'vin' => $vin,
                    'year' => $yearDate->toDateString(),
                    'price' => rand(8000, 60000),
                    'status' => $finalStatus->value,
                ]);

                $started = now()->subDays(rand(5, 30))->startOfDay()->addHours(rand(0, 12));
                $lastPeriod = null;

                $sequence = match ($finalStatus) {
                    Statuses::Delivery => [Statuses::Delivery],
                    Statuses::DeliveryToParking => [Statuses::Delivery, Statuses::DeliveryToParking],
                    Statuses::Parking => [Statuses::Delivery, Statuses::DeliveryToParking, Statuses::Parking],
                    Statuses::Customer, Statuses::Sale => [Statuses::Delivery, Statuses::DeliveryToParking, Statuses::Parking, Statuses::Customer],
                };

                $parking = $parkings->random();
                $customer = $customers->random();

                foreach ($sequence as $index => $statusStep) {
                    if ($index > 0) {
                        $nextStart = (clone $started)->addHours(match ($statusStep) {
                            Statuses::DeliveryToParking => rand(8, 36),
                            Statuses::Parking => rand(4, 24),
                            Statuses::Customer => rand(12, 72),
                            default => rand(4, 24),
                        });
                        $this->endPeriod($lastPeriod, (clone $nextStart)->subHour());
                        $started = $nextStart;
                    }

                    $location = match ($statusStep) {
                        Statuses::Delivery => $sender,
                        Statuses::DeliveryToParking, Statuses::Parking => $parking,
                        Statuses::Customer => $customer,
                        default => $sender,
                    };

                    $lastPeriod = $this->createLocationPeriod(
                        $auto,
                        $location,
                        $statusStep,
                        $started,
                        acceptedBy: $users->random()
                    );
                }

                if ($finalStatus === Statuses::Sale) {
                    AutoSale::query()->create([
                        'auto_id' => $auto->id,
                        'sold_at' => (clone $started)->addHours(rand(1, 8)),
                        'sold_by_user_id' => $users->random()->id,
                        'note' => 'Seeded sale transaction',
                    ]);
                }

                $auto->addMedia($media['photo'])
                    ->preservingOriginal()
                    ->usingFileName('photo_' . $i . '_1.png')
                    ->toMediaCollection('photos');

                $auto->addMedia($media['photo'])
                    ->preservingOriginal()
                    ->usingFileName('photo_' . $i . '_2.png')
                    ->toMediaCollection('photos');

                $auto->addMedia($media['video'])
                    ->preservingOriginal()
                    ->usingFileName('video_' . $i . '.mp4')
                    ->toMediaCollection('videos');

                $auto->addMedia($media['pdf'])
                    ->preservingOriginal()
                    ->usingFileName('doc_' . $i . '.pdf')
                    ->toMediaCollection('documents');

                $auto->addMedia($media['docx'])
                    ->preservingOriginal()
                    ->usingFileName('file_' . $i . '.docx')
                    ->toMediaCollection('documents');
            }
        });
    }

    protected function createLocationPeriod(Auto $auto, object $location, Statuses $status, Carbon $startedAt, ?User $acceptedBy = null): AutoLocationPeriod
    {
        $expectedClass = $status->connectionWithModel();
        if (!($location instanceof $expectedClass)) {
            throw new \InvalidArgumentException('Location class ' . get_class($location) . ' does not match expected ' . $expectedClass . ' for status ' . $status->name);
        }

        return AutoLocationPeriod::query()->create([
            'auto_id' => $auto->id,
            'location_type' => $expectedClass,
            'location_id' => $location->id,
            'status' => $status->value,
            'started_at' => $startedAt,
            'accepted_by_user_id' => $acceptedBy?->id,
            'acceptance_note' => 'Seeded movement to ' . class_basename($location) . ' #' . $location->id,
        ]);
    }

    protected function endPeriod(?AutoLocationPeriod $period, Carbon $endedAt): void
    {
        if (!$period) {
            return;
        }
        $period->ended_at = $endedAt;
        $period->save();
    }

    protected function ensureSampleMediaFiles(): array
    {
        $dir = storage_path('app/seed-media');
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $photo = $dir . DIRECTORY_SEPARATOR . 'photo.png';
        $video = $dir . DIRECTORY_SEPARATOR . 'video.mp4';
        $pdf   = $dir . DIRECTORY_SEPARATOR . 'doc.pdf';
        $docx  = $dir . DIRECTORY_SEPARATOR . 'file.docx';

        if (!file_exists($photo)) {
            $publicPhoto = public_path('images/not_photo.png');
            if (is_file($publicPhoto)) {
                copy($publicPhoto, $photo);
            } else {
                file_put_contents($photo, random_bytes(512));
            }
        }

        if (!file_exists($video)) {
            file_put_contents($video, random_bytes(1024));
        }

        if (!file_exists($pdf)) {
            $content = "%PDF-1.4\n% Seeded PDF\n1 0 obj <<>> endobj\ntrailer <<>>\n%%EOF";
            file_put_contents($pdf, $content);
        }

        if (!file_exists($docx)) {
            file_put_contents($docx, 'Seeded DOCX content');
        }

        return compact('photo', 'video', 'pdf', 'docx');
    }

    protected function generateVin(): string
    {
        $chars = str_split('ABCDEFGHJKLMNPRSTUVWXYZ0123456789');
        $vin = '';
        for ($i = 0; $i < 17; $i++) {
            $vin .= $chars[array_rand($chars)];
        }
        return $vin;
    }

    protected function ensureMinimumReferences(): void
    {
        if (Company::query()->count() === 0) {
            Company::query()->create(['name' => 'Seed Company']);
        }
        if (User::query()->count() === 0) {
            $companyId = Company::query()->inRandomOrder()->value('id');
            User::query()->create([
                'name' => 'Seed Admin',
                'email' => 'seed-admin@example.com',
                'password' => 'password',
                'company_id' => $companyId,
            ]);
        }

        if (Provider::query()->count() === 0) {
            foreach (User::all() as $user) {
                Provider::query()->create([
                    'name' => $user->name,
                    'user_id' => $user->id,
                    'company_id' => $user->company_id,
                ]);
            }
        }
        if (Sender::query()->count() === 0) {
            foreach (User::all() as $user) {
                Sender::query()->create([
                    'name' => $user->name,
                    'user_id' => $user->id,
                    'company_id' => $user->company_id,
                ]);
            }
        }

        if (Color::query()->count() === 0) {
            Color::query()->create(['name' => 'Black', 'name_ru' => 'Черный', 'hex_code' => '#000000']);
            Color::query()->create(['name' => 'White', 'name_ru' => 'Белый', 'hex_code' => '#FFFFFF']);
        }

        if (AutoModel::query()->count() === 0) {
            $brand = AutoBrand::query()->create(['id' => 'SEEDBRAND', 'name' => 'SeedBrand']);
            AutoModel::query()->create(['name' => 'SeedModel A', 'auto_brand_id' => $brand->id]);
            AutoModel::query()->create(['name' => 'SeedModel B', 'auto_brand_id' => $brand->id]);
        }

        if (Parking::query()->count() === 0) {
            $companyId = Company::query()->inRandomOrder()->value('id');
            Parking::query()->create(['title' => 'Seed Parking 1', 'address' => 'Address 1', 'company_id' => $companyId]);
            Parking::query()->create(['title' => 'Seed Parking 2', 'address' => 'Address 2', 'company_id' => $companyId]);
        }

        if (Customer::query()->count() === 0) {
            Customer::query()->create(['name' => 'Seed Customer 1', 'phone' => '111111', 'email' => 'c1@example.com']);
            Customer::query()->create(['name' => 'Seed Customer 2', 'phone' => '222222', 'email' => 'c2@example.com']);
        }
    }
}
