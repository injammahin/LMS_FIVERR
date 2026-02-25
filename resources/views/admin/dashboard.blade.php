@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')

    <div class="space-y-8">

        {{-- PAGE HEADER --}}
        <div>
            <h1 class="text-md font-bold text-slate-800 dark:text-white">LMS Dashboard</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Overview of your learning management system
            </p>
        </div>

        {{-- KPI CARDS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

            {{-- Total Students --}}
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl shadow-sm">
                <p class="text-sm text-slate-500">Total Students</p>
                <h2 class="text-3xl font-bold text-blue-600 mt-2">1,245</h2>
                <p class="text-xs text-green-500 mt-2">+12% this month</p>
            </div>

            {{-- Total Teachers --}}
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl shadow-sm">
                <p class="text-sm text-slate-500">Total Teachers</p>
                <h2 class="text-3xl font-bold text-purple-600 mt-2">58</h2>
                <p class="text-xs text-green-500 mt-2">+4 new teachers</p>
            </div>

            {{-- Active Courses --}}
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl shadow-sm">
                <p class="text-sm text-slate-500">Active Courses</p>
                <h2 class="text-3xl font-bold text-orange-500 mt-2">96</h2>
                <p class="text-xs text-slate-400 mt-2">Across 6 Grades</p>
            </div>

            {{-- Pending Assignments --}}
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl shadow-sm">
                <p class="text-sm text-slate-500">Pending Submissions</p>
                <h2 class="text-3xl font-bold text-red-500 mt-2">214</h2>
                <p class="text-xs text-red-400 mt-2">Needs review</p>
            </div>

        </div>


        {{-- CHART SECTION --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- STUDENT GROWTH (Line Chart) --}}
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl shadow-sm lg:col-span-2">
                <h3 class="font-semibold text-slate-700 dark:text-white mb-4">
                    Student Growth
                </h3>
                <canvas id="studentGrowthChart" height="100"></canvas>
            </div>

            {{-- COURSE DISTRIBUTION (Donut Chart) --}}
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl shadow-sm">
                <h3 class="font-semibold text-slate-700 dark:text-white mb-4">
                    Courses by Grade
                </h3>
                <canvas id="courseDistributionChart"></canvas>
            </div>

        </div>


        {{-- SECOND ROW CHART --}}
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl shadow-sm">
            <h3 class="font-semibold text-slate-700 dark:text-white mb-4">
                Assignment Submissions (Monthly)
            </h3>
            <canvas id="assignmentBarChart" height="90"></canvas>
        </div>


        {{-- RECENT ACTIVITY TABLE --}}
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl shadow-sm">
            <h3 class="font-semibold text-slate-700 dark:text-white mb-4">
                Recent Activity
            </h3>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 border-b border-slate-200 dark:border-slate-700">
                            <th class="py-3">User</th>
                            <th>Action</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-700 dark:text-slate-300">

                        <tr class="border-b border-slate-100 dark:border-slate-800">
                            <td class="py-3">John Doe</td>
                            <td>Submitted Math Assignment</td>
                            <td>Feb 22, 2026</td>
                            <td><span class="px-3 py-1 text-xs bg-green-100 text-green-700 rounded-full">Completed</span>
                            </td>
                        </tr>

                        <tr class="border-b border-slate-100 dark:border-slate-800">
                            <td class="py-3">Sarah Lee</td>
                            <td>Enrolled in Science Course</td>
                            <td>Feb 21, 2026</td>
                            <td><span class="px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded-full">Active</span></td>
                        </tr>

                        <tr>
                            <td class="py-3">Mr. Smith</td>
                            <td>Graded English Assignment</td>
                            <td>Feb 20, 2026</td>
                            <td><span class="px-3 py-1 text-xs bg-purple-100 text-purple-700 rounded-full">Reviewed</span>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>

    </div>

@endsection


@section('scripts')

    <script>

        document.addEventListener("DOMContentLoaded", function () {

            // LINE CHART
            new Chart(document.getElementById('studentGrowthChart'), {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Students',
                        data: [200, 400, 650, 800, 1000, 1245],
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59,130,246,0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } }
                }
            });

            // DONUT CHART
            new Chart(document.getElementById('courseDistributionChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'],
                    datasets: [{
                        data: [12, 15, 18, 14, 20, 17],
                        backgroundColor: [
                            '#3B82F6', '#8B5CF6', '#F59E0B', '#10B981', '#EF4444', '#6366F1'
                        ]
                    }]
                },
                options: {
                    plugins: { legend: { position: 'bottom' } }
                }
            });

            // BAR CHART
            new Chart(document.getElementById('assignmentBarChart'), {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Submissions',
                        data: [120, 190, 300, 250, 400, 350],
                        backgroundColor: '#F97316'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } }
                }
            });

        });

    </script>

@endsection