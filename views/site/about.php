<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'About the Blog';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="about-page">
    <!-- Hero / Intro Section -->
    <section class="about-hero">
        <div class="about-hero-content">
            <h1 class="about-hero-title">About the Blog</h1>
            <p class="about-hero-subtitle">
                This blog is a personal space dedicated to movies that leave a lasting impression. 
                Here, cinema is more than entertainment — it is atmosphere, emotion, and storytelling 
                that stays with you long after the credits roll.
            </p>
        </div>
    </section>

    <!-- About the Blog Section -->
    <section class="about-section">
        <h2 class="about-section-title">What This Blog Is About</h2>
        <div class="about-section-content">
            <p>
                This blog is focused on sharing favorite movies across different genres and eras. 
                It features articles, reviews, and curated selections that highlight films worth watching, 
                revisiting, or discovering for the first time.
            </p>
            <p>
                The goal of the blog is not to rate movies with numbers, but to explore their mood, 
                themes, visual style, and emotional impact. Each article reflects a personal perspective 
                and aims to inspire readers to experience cinema in a more thoughtful way.
            </p>
        </div>
    </section>

    <!-- About the Author Section -->
    <section class="about-section">
        <h2 class="about-section-title">About the Author</h2>
        <div class="about-section-content">
            <p>
                The blog is created and maintained by a movie enthusiast who enjoys exploring films 
                beyond mainstream trends. From iconic classics to hidden gems, the author focuses on 
                movies that stand out through strong storytelling, memorable characters, or unique 
                visual identity.
            </p>
            <p>
                This project is both a creative outlet and a way to share personal recommendations 
                with others who appreciate cinema as an art form.
            </p>
        </div>
    </section>

    <!-- Philosophy and Approach Section -->
    <section class="about-section">
        <h2 class="about-section-title">Philosophy & Approach</h2>
        <div class="about-section-content">
            <ul class="about-philosophy-list">
                <li>Movies are chosen based on personal impression, not popularity alone</li>
                <li>Atmosphere, emotions, and storytelling matter more than ratings</li>
                <li>Each article reflects a subjective but honest point of view</li>
                <li>The blog encourages exploration of different genres and styles</li>
                <li>The main idea is simple: good movies deserve attention, discussion, and a second look.</li>
            </ul>
        </div>
    </section>

    <!-- Technical Information Section -->
    <section class="about-section">
        <h2 class="about-section-title">About the Project</h2>
        <div class="about-section-content">
            <p>
                This blog is developed by Anastasiia Lysenko as a course project using the Yii2 framework 
                and follows the MVC architectural pattern. The application uses MySQL as a database management 
                system and Active Record for database interaction.
            </p>
            <p>
                The project demonstrates practical skills in web application development, user role management, 
                database design, and interface organization.
            </p>
        </div>
    </section>

    <!-- Closing Section -->
    <section class="about-closing">
        <p class="about-closing-text">
            Thank you for visiting the Movie blog. Explore categories, read articles, leave comments, 
            and discover movies that might become your next favorite.
        </p>
    </section>
</div>
