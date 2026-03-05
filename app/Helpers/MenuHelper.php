<?php

namespace App\Helpers;

class MenuHelper
{
    // Main LMS Navigation Items
    public static function getMainNavItems()
    {
        return [
            [
                'icon' => 'dashboard',
                'name' => 'Dashboard',
                'path' => '/admin/dashboard',
            ],
            [
                'icon' => 'courses',
                'name' => 'Course Config',
                'subItems' => [
                    ['name' => 'Divisions', 'path' => '/admin/divisions'],
                    ['name' => 'Subjects', 'path' => '/admin/subjects'],
                    ['name' => 'Courses', 'path' => '/admin/courses'],
                ],
            ],
            [
                'icon' => 'assignments',
                'name' => 'Assignments',
                'subItems' => [
                    // ['name' => 'Manage Assignments', 'path' => '/admin/assignments'],
                    ['name' => 'Graded Assignments', 'path' => '/admin/assignments/graded'],
                ],
            ],
            [
                'icon' => 'students',
                'name' => 'Students',
                'subItems' => [
                    ['name' => 'All Students', 'path' => '/admin/students'],
                    ['name' => 'Add New Student', 'path' => '/admin/students/create'],
                    // ['name' => 'Student Reports', 'path' => '/admin/students/reports'],
                ],
            ],
            [
                'icon' => 'teachers',
                'name' => 'Teachers',
                'subItems' => [
                    ['name' => 'All Teachers', 'path' => '/admin/teachers'],
                    ['name' => 'Add New Teacher', 'path' => '/admin/teachers/create'],
                    // ['name' => 'Teacher Reports', 'path' => '/admin/teachers/reports'],
                    ['name' => 'Assign Courses', 'path' => '/admin/teachers'],
                ],
            ],
            [
                'icon' => 'teachers',
                'name' => 'Satffs',
                'subItems' => [
                    ['name' => 'All staffs', 'path' => '/admin/staffs'],
                    ['name' => 'Add New staffs', 'path' => '/admin/staffs/create'],
                    // ['name' => 'staffs Reports', 'path' => '/admin/staffs/reports'],
                    ['name' => 'Assign Courses', 'path' => '/admin/staffs'],
                ],
            ],
            [
                'icon' => 'analytics',
                'name' => 'Analytics',
                'path' => '/admin/analytics',
            ],
            [
                'icon' => 'reports',
                'name' => 'Reports',
                'subItems' => [
                    ['name' => 'Student Reports', 'path' => '/admin/students/reports'],
                    ['name' => 'Teacher Reports', 'path' => '/admin/teachers/reports'],
                    ['name' => 'staffs Reports', 'path' => '/admin/staffs/reports'],

                ],
            ],
            [
                'icon' => 'ai',
                'name' => 'AI Assistant',
                'subItems' => [
                    ['name' => 'Training (KB)', 'path' => '/admin/ai-assistant/knowledge'],
                    ['name' => 'Upload Files', 'path' => '/admin/ai-assistant/files'],
                    // optional:
                    // ['name' => 'Chat Logs', 'path' => '/admin/ai-assistant/logs'],
                ],
            ],
        ];
    }

    // LMS User Management Items
    // public static function getUserManagementItems()
    // {
    //     return [
    //         [
    //             'icon' => 'users',
    //             'name' => 'User Management',
    //             'subItems' => [
    //                 ['name' => 'View Users', 'path' => '/admin/user-management'],
    //                 ['name' => 'Roles and Permissions', 'path' => '/admin/user-management/roles'],
    //             ],
    //         ],
    //         [
    //             'icon' => 'settings',
    //             'name' => 'Settings',
    //             'subItems' => [
    //                 ['name' => 'General Settings', 'path' => '/admin/settings/general'],
    //                 ['name' => 'Notification Settings', 'path' => '/admin/settings/notifications'],
    //             ],
    //         ],
    //     ];
    // }

    // Combining all Menu Items into Groups
    public static function getMenuGroups()
    {
        return [
            [
                'title' => 'Learning',
                'items' => self::getMainNavItems()
            ],
            // [
            //     'title' => 'Management',
            //     'items' => self::getUserManagementItems()
            // ],
        ];
    }

    // Check if current page is active
    public static function isActive($path)
    {
        return request()->is(ltrim($path, '/'));
    }

    // Fetch the icon SVG based on icon name
    public static function getIconSvg($iconName)
    {
        $icons = [
            // Dashboard
            'dashboard' => self::svg('
                <path d="M4 13h7V4H4v9Zm9 7h7V11h-7v9ZM4 20h7v-5H4v5Zm9-11h7V4h-7v5Z"/>
            '),

            // Courses / Course Config
            'courses' => self::svg('
                <path d="M4 6.5C4 5.12 5.12 4 6.5 4H20v14H6.5C5.12 18 4 16.88 4 15.5v-9Zm2.5-.5a.5.5 0 0 0-.5.5v9c0 .28.22.5.5.5H18V6H6.5Z"/>
                <path d="M8 8h8v2H8V8Zm0 4h8v2H8v-2Z"/>
            '),

            // Assignments
            'assignments' => self::svg('
                <path d="M7 4h10a2 2 0 0 1 2 2v14H5V6a2 2 0 0 1 2-2Zm0 2v12h10V6H7Z"/>
                <path d="M9 8h6v2H9V8Zm0 4h6v2H9v-2Z"/>
            '),

            // Students
            'students' => self::svg('
                <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5Z"/>
            '),

            // Teachers
            'teachers' => self::svg('
                <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm-7 9v-1c0-2.76 3.13-5 7-5s7 2.24 7 5v1H5Z"/>
                <path d="M17 7h4v2h-4V7Zm0 4h4v2h-4v-2Z"/>
            '),

            // Analytics
            'analytics' => self::svg('
                <path d="M5 19V5h2v14H5Zm6 0V9h2v10h-2Zm6 0V12h2v7h-2Z"/>
                <path d="M4 20h16v2H4v-2Z"/>
            '),

            // Reports
            'reports' => self::svg('
                <path d="M6 2h9l5 5v15a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2Zm8 1v5h5"/>
                <path d="M8 12h8v2H8v-2Zm0 4h8v2H8v-2Z"/>
            '),

            // Users (User Management)
            'users' => self::svg('
                <path d="M16 11a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm-8 1a3 3 0 1 0-3-3 3 3 0 0 0 3 3Z"/>
                <path d="M16 13c-3.87 0-7 2.13-7 4.75V20h14v-2.25C23 15.13 19.87 13 16 13Z"/>
                <path d="M8 14c-2.76 0-5 1.57-5 3.5V20h6v-2c0-1.02.38-2.01 1.05-2.83A6.5 6.5 0 0 0 8 14Z"/>
            '),

            // Settings
            'settings' => self::svg('
                <path d="M19.14 12.94a7.49 7.49 0 0 0 0-1.88l2.03-1.58a.5.5 0 0 0 .12-.64l-1.92-3.32a.5.5 0 0 0-.6-.22l-2.39.96a7.12 7.12 0 0 0-1.62-.94L14.5 2.5a.5.5 0 0 0-.5-.5h-4a.5.5 0 0 0-.5.5l-.36 2.82a7.12 7.12 0 0 0-1.62.94l-2.39-.96a.5.5 0 0 0-.6.22L2.61 8.84a.5.5 0 0 0 .12.64l2.03 1.58a7.49 7.49 0 0 0 0 1.88l-2.03 1.58a.5.5 0 0 0-.12.64l1.92 3.32a.5.5 0 0 0 .6.22l2.39-.96a7.12 7.12 0 0 0 1.62.94l.36 2.82a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 .5-.5l.36-2.82a7.12 7.12 0 0 0 1.62-.94l2.39.96a.5.5 0 0 0 .6-.22l1.92-3.32a.5.5 0 0 0-.12-.64l-2.03-1.58ZM12 15.5A3.5 3.5 0 1 1 15.5 12 3.5 3.5 0 0 1 12 15.5Z"/>
            '),
            'ai' => self::svg('
                <path d="M12 2a4 4 0 0 1 4 4v1h1a3 3 0 0 1 3 3v4a3 3 0 0 1-3 3h-1v1a4 4 0 0 1-8 0v-1H7a3 3 0 0 1-3-3v-4a3 3 0 0 1 3-3h1V6a4 4 0 0 1 4-4Zm2 5V6a2 2 0 1 0-4 0v1h4Zm-4 12v1a2 2 0 1 0 4 0v-1h-4Zm7-10h-1v2h-2V9H10v2H8V9H7a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1h1v-2h2v2h4v-2h2v2h1a1 1 0 0 0 1-1v-4a1 1 0 0 0-1-1Z"/>
            '),
        ];

        return $icons[$iconName] ?? self::svg('<path d="M12 2l3 7 7 3-7 3-3 7-3-7-7-3 7-3 3-7Z"/>');
    }

    /**
     * Wrap raw paths into a consistent SVG tag.
     */
    private static function svg(string $paths): string
    {
        return '<svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">'
            . $paths .
            '</svg>';
    }
}