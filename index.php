<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/layout.php';

renderHeader("Home");
renderNavbar();
?>

<head>
    <script src="https://kit.fontawesome.com/8368f69198.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<section id="home" class="min-h-screen flex flex-col justify-center items-center pt-24 bg-gradient-to-br from-orange-50 via-white to-blue-100 relative overflow-hidden">

    <div class="max-w-4xl text-center px-6 z-10">

        <div class="text-3xl font-bold text-[#0A2A6B] uppercase">
            MoneyKids Platform
        </div>

        <h1 class="text-5xl md:text-6xl font-black text-[#0A2A6B] mt-6 leading-tight">
            Teach Kids Smart
            <span class="text-orange-500">Money Habits</span>
        </h1>

        <p class="text-gray-600 mt-6 text-lg">
            A smart platform that helps parents manage pocket money, track expenses and teach financial responsibility.
        </p>

      
        <div class="mt-8 flex flex-col items-center">
    
    <!-- Input -->
    <div class="mt-8 flex justify-center">
    <button class="group px-6 py-3 bg-gradient-to-r from-blue-600 to-orange-500 text-white rounded-full font-semibold hover:from-blue-700 hover:to-orange-600 flex items-center gap-2 transition">
        Get Started
        <i class="fa-solid fa-arrow-right transition-transform duration-300 group-hover:translate-x-1"></i>
    </button>
</div>
</section>

<!-- FEATURES -->
<section id="features" class="py-20 bg-white">

    <div class="text-center mb-12">
        <h2 class="text-4xl md:text-5xl font-black text-[#0A2A6B] mb-6 uppercase">Features</h2>
    </div>

    <div class="max-w-6xl mx-auto grid md:grid-cols-3 gap-8 px-6">

        <div class="flex flex-col lg:flex-row items-center gap-12 p-16 rounded-3xl hover:shadow-2xl transition-all duration-300 border-2 border-gray-100 hover:border-blue-200 bg-gradient-to-br from-white to-blue-50/30">
            <h3 class="text-4xl font-bold text-[#0A2A6B] mb-6">Pocket Money</h3>
            <p class="text-xl text-gray-600 mb-8 leading-relaxed">Assign allowance easily</p>
        </div>

        <div class="flex flex-col lg:flex-row-reverse items-center gap-12 p-16 rounded-3xl hover:shadow-2xl transition-all duration-300 border-2 border-gray-100 hover:border-orange-200 bg-gradient-to-br from-white to-orange-50/30">
            <h3 class="text-4xl font-bold text-[#0A2A6B] mb-6">Expense Tracking</h3>
            <p class="text-xl text-gray-600 mb-8 leading-relaxed">Monitor spending</p>
        </div>

        <div class="flex flex-col lg:flex-row items-center gap-12 p-16 rounded-3xl hover:shadow-2xl transition-all duration-300 border-2 border-gray-100 hover:border-teal-200 bg-gradient-to-br from-white to-purple-50/30">
            <h3 class="text-4xl font-bold text-[#0A2A6B] mb-6">Goals</h3>
            <p class="text-xl text-gray-600 mb-8 leading-relaxed">Save for objectives</p>
        </div>

    </div>

</section>

<!-- HOW IT WORKS -->
<section class="py-20 bg-gradient-to-br from-blue-50 to-orange-50" id="works">

    <div class="text-center mb-12">
        <h2 class="text-4xl md:text-5xl font-black text-[#0A2A6B] mb-6 uppercase">How It Works</h2>
    </div>

    <div class="max-w-5xl mx-auto grid md:grid-cols-3 gap-8 px-6 text-center">

        <div class="text-center group hover:scale-105 transition-all duration-300 p-6 rounded-3xl hover:shadow-xl border-2 border-gray-100 hover:border-blue-200 bg-white">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-blue-800 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">1</div>
            <p class="mt-2 font-semibold">Parent creates account</p>
        </div>

        <div class="text-center group hover:scale-105 transition-all duration-300 p-6 rounded-3xl hover:shadow-xl border-2 border-gray-100 hover:border-orange-200 bg-white">
            <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-orange-700 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">2</div>
            <p class="mt-2 font-semibold">Add children</p>
        </div>

        <div class="text-center group hover:scale-105 transition-all duration-300 p-6 rounded-3xl hover:shadow-xl border-2 border-gray-100 hover:border-teal-200 bg-white">
            <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-700 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">3</div>
            <p class="mt-2 font-semibold">Track & learn</p>
        </div>

    </div>

</section>

<?php renderFooter(); ?>