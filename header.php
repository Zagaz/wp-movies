<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php bloginfo('name'); ?><?php wp_title('|', true, 'left'); ?></title>
  <?php wp_head(); ?>
</head>
<body class="bg-gray-950 text-white">

<header class="w-full bg-amber-200 shadow  px-[20px] py-[20px] sm:px-8 sm:py-8 flex flex-col sm:flex-row items-center justify-between gap-4 mb-10">
  <div class="flex flex-row items-center w-full sm:w-auto justify-between">
  <img src = "<?php echo get_theme_file_uri(); ?>/assets/logo.png" alt = "<?php bloginfo('name'); ?>" 
  class=" w-20 h-auto"
  /> 
  <div class="ml-5"> 
      <h1 class="text-2xl sm:text-3xl font-bold text-gray-800"><?php bloginfo('name'); ?></h1>
      <p class="text-gray-600 text-sm sm:text-base mt-1">Explore your favorite movies and actors</p>
    </div>

    <!-- Hamburger Button -->
    <button id="menu-toggle" class="sm:hidden ml-4 p-2 rounded hover:bg-amber-300 focus:outline-none focus:ring-2 focus:ring-blue-500" aria-label="Open menu">
      <svg class="w-7 h-7 text-gray-800" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
    </button>
  </div>
  <!-- Navigation -->
  <nav id="main-nav" class=" text-white w-full sm:w-auto mt-4 sm:mt-0 flex-col sm:flex-row flex gap-2 sm:gap-6 items-center justify-center sm:justify-end transition-all duration-300 hidden sm:flex">
    <a class = "text-black" href="<?php echo home_url(); ?>" class="nav-link">Home</a>
    <a class = "text-black" href="<?php echo get_post_type_archive_link('movie'); ?>" class="nav-link">Movies</a>
    <a class = "text-black" href="<?php echo get_post_type_archive_link('actor'); ?>" class="nav-link">Actors</a>
  </nav>
</header>

<div class="max-w-6xl mx-auto px-4">
  <!-- Page content starts here -->
</div>

<style>
  .nav-link {
    @apply text-blue-700 font-medium px-3 py-2 rounded transition duration-200;
    position: relative;
    overflow: hidden;
  }
  .nav-link::after {
    content: '';
    display: block;
    position: absolute;
    left: 50%;
    bottom: 0;
    width: 0;
    height: 2px;
    background: #2563eb; /* blue-600 */
    transition: width 0.3s, left 0.3s;
  }
  .nav-link:hover,
  .nav-link:focus {
    color: #fff;
    background: #2563eb;
  }
  .nav-link:hover::after,
  .nav-link:focus::after {
    width: 100%;
    left: 0;
    background: #fff;
  }
</style>
<script>
  // Hamburger menu toggle
  document.addEventListener('DOMContentLoaded', function () {
    const menuToggle = document.getElementById('menu-toggle');
    const mainNav = document.getElementById('main-nav');
    menuToggle.addEventListener('click', function () {
      mainNav.classList.toggle('hidden');
      mainNav.classList.toggle('flex');
    });
  });
</script>