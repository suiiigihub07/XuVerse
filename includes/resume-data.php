<?php
require_once __DIR__ . '/content.php';

function xuverse_resume_data($conn)
{
    $copy = xuverse_content('copy');
    $profile = xuverse_content('profile');
    $site = xuverse_content('site');
    $portfolio = xuverse_find_public('projects', 'xuverse');
    $education = $profile['education'];
    $roleGroups = ['Experience' => [], 'Leadership' => []];

    foreach ($profile['roles'] as $role) {
        $heading = ($role['category'] ?? '') === 'work' ? 'Experience' : 'Leadership';
        $roleGroups[$heading][] = [
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
            'description' => $education['focus'],
        ]],
        'areas' => [
            'Software' => ['Front-end', 'Back-end', 'Database design', 'Software development', 'UI/UX'],
            'Creative' => ['Photography', 'Videography', 'Video direction', 'Video editing', 'Digital media'],
        ],
        'tools' => ['GitHub', 'VS Code', 'DaVinci Resolve', 'CapCut', 'Canva'],
        'achievement' => $profile['achievement']['title'],
        'achievement_note' => 'Award reported by ' . $copy['name'] . '.',
    ];
}
