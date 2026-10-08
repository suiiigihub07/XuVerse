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
    $copy = xuverse_content('copy'); $profile = xuverse_content('profile');
    $settings = xuverse_public_settings();
    $experience = [];
    foreach ($profile['roles'] as $role) {
        $experience[] = ['job_title'=>$role['title'], 'company_name'=>$role['organisation'], 'start_date'=>'', 'end_date'=>'', 'description'=>$role['dates']];
    }
    $projects = [];
    foreach (xuverse_content('projects') as $p) { $projects[] = ['id'=>$p['legacy_ids'][0], 'slug'=>$p['slug'], 'title'=>$p['title'], 'description'=>$p['summary']]; }
    return [
        'settings'=>$settings, 'user'=>['full_name'=>$copy['name'], 'avatar_path'=>'assets/images/public/portrait.webp'],
        'name'=>$copy['name'], 'headline'=>'Intelligence Computing undergraduate', 'summary'=>$copy['resume_summary'],
        'email'=>$settings['contact_email'], 'location'=>$copy['location'], 'focus'=>$copy['current_focus'], 'website'=>xuverse_base_url(),
        'experience'=>$experience, 'education'=>[['institution'=>$profile['education']['institution'], 'degree'=>$profile['education']['program'], 'start_year'=>$profile['education']['start'], 'end_year'=>$profile['education']['end'], 'description'=>$profile['education']['level']]],
        'skills'=>xuverse_content('skills'), 'projects'=>$projects, 'achievement'=>$profile['achievement']['title']
    ];
}
