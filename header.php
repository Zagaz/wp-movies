<!DOCTYPE html>
<html lang="en">
<head>
  <?php wp_head(); ?>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Document</title>
</head>
<body class="bg-gray-100 text-gray-900">


  <div class="container mx-auto ">
    <header class="mb-8 p-10 grid bg-amber-200">
      <h1 class="text-4xl font-bold text-center text-gray-800">Welcome to the Movie Database</h1>
      <p class="text-center text-gray-600 mt-2">Explore your favorite movies and actors</p>
      <nav class="mt-4">
     
          <a href="<?php echo home_url(); ?>" class="text-blue-600 hover:underline">Home</a></li>
          <a href="<?php echo get_post_type_archive_link('movie'); ?>" class="text-blue-600 hover:underline">Movies</a></li>
          <a href="<?php echo get_post_type_archive_link('actor'); ?>" class="text-blue-600 hover:underline">Actors</a></li>   
    </header>

    

