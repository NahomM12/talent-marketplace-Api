<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * The six service categories GM Bridge offers. Slugs are stable identifiers
     * used in URLs and API filters, so they are fixed strings rather than
     * generated.
     */
    public function run(): void
    {
        $services = [
            [
                'name' => 'Graphic Design',
                'slug' => 'graphic-design',
                'description' => 'Brand identities, marketing collateral, and social media creatives crafted by skilled designers.',
                'icon' => 'palette',
            ],
            [
                'name' => 'Video Editing',
                'slug' => 'video-editing',
                'description' => 'Long-form, short-form, and promotional video editing tailored to your platform and audience.',
                'icon' => 'video',
            ],
            [
                'name' => 'Virtual Assistance',
                'slug' => 'virtual-assistance',
                'description' => 'Reliable administrative, scheduling, and operational support to keep your business running.',
                'icon' => 'briefcase',
            ],
            [
                'name' => 'SDR/BDR',
                'slug' => 'sdr-bdr',
                'description' => 'Outbound prospecting and pipeline-building specialists who fill your sales funnel.',
                'icon' => 'target',
            ],
            [
                'name' => 'Customer Support',
                'slug' => 'customer-support',
                'description' => 'Friendly, responsive support across email, chat, and phone to delight your customers.',
                'icon' => 'headset',
            ],
            [
                'name' => 'Translation',
                'slug' => 'translation',
                'description' => 'Accurate, culturally aware translation and localization across major global languages.',
                'icon' => 'globe',
            ],
            [
              'name' => 'English as a Second Language (ESL)',
              'slug' => 'english-as-a-second-language',
              'description' => 'Practical English instruction for learners looking to improve their speaking, writing, reading, listening, and overall communication skills.',
              'icon' => 'languages',
            ],
        ];

        foreach ($services as $service) {
            Service::query()->updateOrCreate(['slug' => $service['slug']], $service);
        }
    }
}
