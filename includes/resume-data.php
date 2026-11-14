<?php
require_once __DIR__ . '/content.php';

function xuverse_resume_data($conn)
{
    $copy = xuverse_content('copy');
    $profile = xuverse_content('profile');
    $site = xuverse_content('site');
    $portfolio = xuverse_find_public('projects', 'xuverse');
    $education = $profile['education'];
    $roleGroups = [
        'work' => ['label' => 'Work Experience', 'entries' => []],
        'leadership' => ['label' => 'Leadership & Activities', 'entries' => []],
    ];

    foreach ($profile['roles'] as $role) {
        $category = $role['category'];
        $roleGroups[$category]['entries'][] = [
            'job_title' => $role['title'],
            'company_name' => $role['organisation'],
            'dates' => $role['dates'],
            'description' => $role['description'] ?? '',
        ];
    }

    return [
        'settings' => xuverse_public_settings(),
        'name' => $copy['name'],
        'headline' => $site['headline'],
        'summary' => $copy['resume_summary'],
        'email' => substr(xuverse_content('links')['email'], 7),
        'location' => $copy['location'],
        'website' => rtrim($portfolio['live'] ?? xuverse_base_url(), '/'),
        'role_groups' => $roleGroups,
        'education' => [[
            'institution' => $education['institution'],
            'degree' => $education['level'] . ' — ' . $education['program'],
            'start_year' => $education['start'],
            'end_year' => $education['end'],
            'location' => $education['location'],
            'semester' => $education['semester'],
        ]],
        'areas' => [
            'Software' => ['Front-end', 'Back-end', 'Database design', 'Software development', 'UI/UX'],
            'Creative' => ['Photography', 'Videography', 'Video direction', 'Video editing', 'Digital media'],
        ],
        'tools' => ['GitHub', 'VS Code', 'DaVinci Resolve', 'CapCut', 'Canva'],
    ];
}
