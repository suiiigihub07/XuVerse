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
    $settings = $conn->query('SELECT * FROM settings LIMIT 1')->fetch_assoc() ?: [];
    $user = $conn->query("SELECT full_name, avatar_path FROM users WHERE role='admin' ORDER BY id LIMIT 1")->fetch_assoc() ?: [];
    $skills = $conn->query("SELECT category, skill_name FROM skills ORDER BY FIELD(category, 'Technology', 'Communication', 'Language', 'Creative', 'Personal'), category, skill_name")->fetch_all(MYSQLI_ASSOC);
    $skillsByCategory = [];
    foreach ($skills as $skill) {
        if (trim((string)$skill['skill_name']) !== '') {
            $skillsByCategory[trim((string)$skill['category']) ?: 'Skills'][] = $skill['skill_name'];
        }
    }

    return [
        'settings' => $settings,
        'user' => $user,
        'name' => xuverse_setting($settings, 'hero_title', $user['full_name'] ?? 'Resume'),
        'headline' => xuverse_setting($settings, 'resume_headline', xuverse_setting($settings, 'hero_subtitle')),
        'summary' => xuverse_setting($settings, 'resume_summary', xuverse_setting($settings, 'hero_description')),
        'email' => xuverse_setting($settings, 'contact_email'),
        'location' => xuverse_setting($settings, 'location_text'),
        'focus' => xuverse_setting($settings, 'current_focus'),
        'website' => xuverse_base_url(),
        'experience' => $conn->query('SELECT * FROM experience ORDER BY start_date DESC, id DESC')->fetch_all(MYSQLI_ASSOC),
        'education' => $conn->query('SELECT * FROM education ORDER BY start_year DESC, id DESC')->fetch_all(MYSQLI_ASSOC),
        'skills' => $skillsByCategory,
        'projects' => $conn->query('SELECT * FROM projects WHERE is_published=1 ORDER BY display_order, id DESC LIMIT 3')->fetch_all(MYSQLI_ASSOC),
    ];
}
