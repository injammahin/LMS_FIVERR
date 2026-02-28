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
                'path' => '/admin/dashboard',  // Added 'admin/' prefix
            ],
            // [
            //     'icon' => 'courses',
            //     'name' => 'Courses',
            //     'subItems' => [
            //         ['name' => 'All Courses', 'path' => '/admin/courses'],  // Added 'admin/' prefix
            //         ['name' => 'Add New Course', 'path' => '/admin/courses/create'],  // Added 'admin/' prefix
            //     ],
            // ],
            [
                'icon' => 'courses',
                'name' => 'Course Config',
                'subItems' => [
                    ['name' => 'Divisions', 'path' => '/admin/divisions'], // Added 'admin/' prefix
                    ['name' => 'Subjects', 'path' => '/admin/subjects'],
                    ['name' => 'Courses', 'path' => '/admin/courses'],

                ],
            ],
            [
                'icon' => 'assignments',
                'name' => 'Assignments',
                'subItems' => [
                    ['name' => 'Manage Assignments', 'path' => '/admin/assignments'],  // Added 'admin/' prefix
                    ['name' => 'Graded Assignments', 'path' => '/admin/assignments/graded'],  // Added 'admin/' prefix
                    ['name' => 'Assignment Templates', 'path' => '/admin/assignments/templates'],  // Added 'admin/' prefix
                ],
            ],
            [
                'icon' => 'students',
                'name' => 'Students',
                'subItems' => [
                    ['name' => 'All Students', 'path' => '/admin/students'],  // Added 'admin/' prefix
                    ['name' => 'Add New Student', 'path' => '/admin/students/create'],  // Added 'admin/' prefix
                    ['name' => 'Student Reports', 'path' => '/admin/students/reports'],  // Added 'admin/' prefix
                ],
            ],
            [
                'icon' => 'teachers',
                'name' => 'Teachers',
                'subItems' => [
                    ['name' => 'All Teachers', 'path' => '/admin/teachers'],  // Added 'admin/' prefix
                    ['name' => 'Add New Teacher', 'path' => '/admin/teachers/create'],  // Added 'admin/' prefix
                    ['name' => 'Teacher Reports', 'path' => '/admin/teachers/reports'],  // Added 'admin/' prefix
                    ['name' => 'Assign Courses', 'path' => '/admin/teachers'], 

                ],
            ],
            [
                'icon' => 'calendar',
                'name' => 'Calendar',
                'path' => '/admin/calendar',  // Added 'admin/' prefix
            ],
            [
                'icon' => 'reports',
                'name' => 'Reports',
                'subItems' => [
                    ['name' => 'Course Reports', 'path' => '/admin/reports/courses'],  // Added 'admin/' prefix
                    ['name' => 'Student Reports', 'path' => '/admin/reports/students'],  // Added 'admin/' prefix
                    ['name' => 'Teacher Reports', 'path' => '/admin/reports/teachers'],  // Added 'admin/' prefix
                    ['name' => 'Assignments Reports', 'path' => '/admin/reports/assignments'],  // Added 'admin/' prefix
                ],
            ],
        ];
    }

    // LMS User Management Items
    public static function getUserManagementItems()
    {
        return [
            [
                'icon' => 'users',
                'name' => 'User Management',
                'subItems' => [
                    ['name' => 'View Users', 'path' => '/admin/user-management'],  // Added 'admin/' prefix
                    ['name' => 'Roles and Permissions', 'path' => '/admin/user-management/roles'],  // Added 'admin/' prefix
                ],
            ],
            [
                'icon' => 'settings',
                'name' => 'Settings',
                'subItems' => [
                    ['name' => 'General Settings', 'path' => '/admin/settings/general'],  // Added 'admin/' prefix
                    ['name' => 'Payment Settings', 'path' => '/admin/settings/payment'],  // Added 'admin/' prefix
                    ['name' => 'Notification Settings', 'path' => '/admin/settings/notifications'],  // Added 'admin/' prefix
                ],
            ],
        ];
    }

    // LMS Other Items (Features that support the LMS)
    public static function getOtherItems()
    {
        return [
            [
                'icon' => 'chat',
                'name' => 'Discussions',
                'subItems' => [
                    ['name' => 'Forums', 'path' => '/admin/discussions/forums'],  // Added 'admin/' prefix
                    ['name' => 'Messages', 'path' => '/admin/discussions/messages'],  // Added 'admin/' prefix
                ],
            ],
            [
                'icon' => 'media',
                'name' => 'Media Library',
                'path' => '/admin/media-library',  // Added 'admin/' prefix
            ],
            [
                'icon' => 'file',
                'name' => 'Documents',
                'subItems' => [
                    ['name' => 'Upload Documents', 'path' => '/admin/documents/upload'],  // Added 'admin/' prefix
                    ['name' => 'View Documents', 'path' => '/admin/documents'],  // Added 'admin/' prefix
                ],
            ],
        ];
    }

    // Combining all Menu Items into Groups
    public static function getMenuGroups()
    {
        return [
            [
                'title' => 'Learning',
                'items' => self::getMainNavItems()
            ],
            [
                'title' => 'Management',
                'items' => self::getUserManagementItems()
            ],
            [
                'title' => 'Support',
                'items' => self::getOtherItems()
            ]
        ];
    }

    // Function to check if the current page is active
    public static function isActive($path)
    {
        return request()->is(ltrim($path, '/'));
    }

    // Function to fetch the icon SVG based on the icon name
    public static function getIconSvg($iconName)
    {
        $icons = [
            'dashboard' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V8.99998C3.25 10.2426 4.25736 11.25 5.5 11.25H9C10.2426 11.25 11.25 10.2426 11.25 8.99998V5.5C11.25 4.25736 10.2426 3.25 9 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H9C9.41421 4.75 9.75 5.08579 9.75 5.5V8.99998C9.75 9.41419 9.41421 9.74998 9 9.74998H5.5C5.08579 9.74998 4.75 9.41419 4.75 8.99998V5.5ZM5.5 12.75C4.25736 12.75 3.25 13.7574 3.25 15V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H9C10.2426 20.75 11.25 19.7427 11.25 18.5V15C11.25 13.7574 10.2426 12.75 9 12.75H5.5ZM4.75 15C4.75 14.5858 5.08579 14.25 5.5 14.25H9C9.41421 14.25 9.75 14.5858 9.75 15V18.5C9.75 18.9142 9.41421 19.25 9 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V15ZM12.75 5.5C12.75 4.25736 13.7574 3.25 15 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V8.99998C20.75 10.2426 19.7426 11.25 18.5 11.25H15C13.7574 11.25 12.75 10.2426 12.75 8.99998V5.5ZM15 4.75C14.5858 4.75 14.25 5.08579 14.25 5.5V8.99998C14.25 9.41419 14.5858 9.74998 15 9.74998H18.5C18.9142 9.74998 19.25 9.41419 19.25 8.99998V5.5C19.25 5.08579 18.9142 4.75 18.5 4.75H15ZM15 12.75C13.7574 12.75 12.75 13.7574 12.75 15V18.5C12.75 19.7426 13.7574 20.75 15 20.75H18.5C19.7426 20.75 20.75 19.7427 20.75 18.5V15C20.75 13.7574 19.7426 12.75 18.5 12.75H15ZM14.25 15C14.25 14.5858 14.5858 14.25 15 14.25H18.5C18.9142 14.25 19.25 14.5858 19.25 15V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15C14.5858 19.25 14.25 18.9142 14.25 18.5V15Z" fill="currentColor"></path></svg>',
            // More icons based on your LMS features...
        ];

        return $icons[$iconName] ?? '<svg width="1em" height="1em" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" fill="currentColor"/></svg>';
    }
}