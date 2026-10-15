<?php

require_once __DIR__ . '/functions.php';

function xuverse_resume_date($value, $empty = 'Present')
{
    $value = trim((string)$value);
    if ($value === '' || $value === '0000-00-00') {
        return $empty;
    }

    $timestamp = strtotime($value);
    return $timestamp === false ? $value : date('M Y', $timestamp);
}

function xuverse_resume_data($conn)
{
    require_once __DIR__ . '/content.php';
    $copy = xuverse_content('copy'); $profile = xuverse_content('profile'); $site = xuverse_content('site');
    $settings = xuverse_public_settings();
    $portfolio = xuverse_find_public('projects', 'xuverse');
    $experience = [];
    foreach ($profile['roles'] as $role) {
        $experience[] = ['job_title'=>$role['title'], 'company_name'=>$role['organisation'], 'dates'=>$role['dates'], 'category'=>$role['category'] ?? 'leadership', 'start_date'=>'', 'end_date'=>'', 'description'=>$role['description'] ?? ''];
    }
    $projects = [];
    foreach (xuverse_published('projects') as $p) { $projects[] = ['id'=>$p['legacy_ids'][0] ?? 0, 'slug'=>$p['slug'], 'title'=>$p['title'], 'description'=>$p['summary']]; }
    return [
        'settings'=>$settings, 'user'=>['full_name'=>$copy['name'], 'avatar_path'=>$site['portrait']],
        'name'=>$copy['name'], 'headline'=>$site['headline'], 'summary'=>$copy['resume_summary'],
        'email'=>$settings['contact_email'], 'location'=>$copy['location'], 'focus'=>$copy['current_focus'], 'website'=>rtrim($portfolio['live'] ?? xuverse_base_url(), '/'),
        'experience'=>$experience,
        'role_groups'=>[
            'Leadership & Creative Roles'=>array_values(array_filter($experience, fn($role) => $role['category'] === 'leadership')),
            'Experience / Work'=>array_values(array_filter($experience, fn($role) => $role['category'] === 'work'))
        ],
        'education'=>[['institution'=>$profile['education']['institution'], 'degree'=>$profile['education']['level'] . ' — ' . $profile['education']['program'], 'start_year'=>$profile['education']['start'], 'end_year'=>$profile['education']['end'], 'location'=>$profile['education']['location'], 'semester'=>$profile['education']['semester'], 'description'=>$profile['education']['focus']]],
        'skills'=>xuverse_content('skills'), 'projects'=>$projects, 'achievement'=>$profile['achievement']['title'], 'achievement_note'=>str_replace(' Do not add them.', '', $profile['achievement']['note'])
    ];
}
