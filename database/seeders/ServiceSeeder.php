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
                'type' => 'managed_services',
                'is_active' => true,
                'inclusions' => ['Logo and brand identity design', 'Social media graphics', 'Marketing collateral', 'Source design files'],
            ],
            [
                'name' => 'Video Editing',
                'slug' => 'video-editing',
                'description' => 'Long-form, short-form, and promotional video editing tailored to your platform and audience.',
                'type' => 'managed_services',
                'is_active' => true,
                'inclusions' => ['Footage assembly and trimming', 'Color and audio correction', 'Titles and captions', 'Platform-ready exports'],
            ],
            [
                'name' => 'Virtual Assistance',
                'slug' => 'virtual-assistance',
                'description' => 'Reliable administrative, scheduling, and operational support to keep your business running.',
                'type' => 'remote_talent',
                'is_active' => true,
                'inclusions' => ['Inbox and calendar management', 'Meeting coordination', 'Document preparation', 'Routine operations support'],
            ],
            [
                'name' => 'SDR/BDR',
                'slug' => 'sdr-bdr',
                'description' => 'Outbound prospecting and pipeline-building specialists who fill your sales funnel.',
                'type' => 'remote_talent',
                'is_active' => true,
                'inclusions' => ['Prospect research', 'Outbound calls and email', 'Lead qualification', 'CRM activity tracking'],
            ],
            [
                'name' => 'Customer Support',
                'slug' => 'customer-support',
                'description' => 'Friendly, responsive support across email, chat, and phone to delight your customers.',
                'type' => 'remote_talent',
                'is_active' => true,
                'inclusions' => ['Email and chat support', 'Ticket triage and follow-up', 'Customer issue resolution', 'Support handoff notes'],
            ],
            [
                'name' => 'Translation',
                'slug' => 'translation',
                'description' => 'Accurate, culturally aware translation and localization across major global languages.',
                'type' => 'managed_services',
                'is_active' => true,
                'inclusions' => ['Document translation', 'Terminology consistency', 'Cultural localization', 'Proofreading and quality review'],
            ],
            [
                'name' => 'English as a Second Language (ESL)',
                'slug' => 'english-as-a-second-language',
                'description' => 'Practical English instruction for learners looking to improve their speaking, writing, reading, listening, and overall communication skills.',
                'type' => 'managed_services',
                'is_active' => true,
                'inclusions' => ['Speaking and listening practice', 'Reading and writing lessons', 'Grammar and vocabulary support', 'Progress feedback'],
            ],
            [
                'name' => 'Administrative Support',
                'slug' => 'administrative-support',
                'description' => 'Remote administrative professionals supporting business operations.',
                'type' => 'remote_talent',
                'is_active' => true,
                'inclusions' => ['Calendar and email management', 'Document preparation and filing', 'Meeting coordination', 'Data entry and reporting'],
            ]
        ];

        foreach ($services as $service) {
            Service::query()->updateOrCreate(['slug' => $service['slug']], $service);
        }
    }
}
