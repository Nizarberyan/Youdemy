<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Youdemy - Where Learning Gets Exciting!</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.10.4/index.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ScrollTrigger/1.0.6/ScrollTrigger.min.js"></script>
    <style>
        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        @keyframes pulse-border {
            0% {
                border-color: rgba(147, 51, 234, 0.3);
            }

            50% {
                border-color: rgba(147, 51, 234, 0.8);
            }

            100% {
                border-color: rgba(147, 51, 234, 0.3);
            }
        }

        @keyframes wave {
            0% {
                transform: rotate(0deg);
            }

            25% {
                transform: rotate(20deg);
            }

            75% {
                transform: rotate(-15deg);
            }

            100% {
                transform: rotate(0deg);
            }
        }

        @keyframes sparkle {

            0%,
            100% {
                opacity: 0;
                transform: scale(0.5);
            }

            50% {
                opacity: 1;
                transform: scale(1.2);
            }
        }

        @keyframes bounce-horizontal {

            0%,
            100% {
                transform: translateX(0);
            }

            50% {
                transform: translateX(3px);
            }
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        .animate-wave {
            animation: wave 2s ease-in-out infinite;
            transform-origin: 70% 70%;
        }

        .animate-pulse-border {
            animation: pulse-border 2s ease-in-out infinite;
        }

        .card-hover-effect:hover .card-icon {
            transform: translateY(-10px) scale(1.1) rotate(10deg);
        }

        .scroll-trigger {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease-out;
        }

        .scroll-trigger.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .animate-sparkle {
            position: relative;
        }

        .animate-sparkle::before,
        .animate-sparkle::after {
            content: '✨';
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.2rem;
            pointer-events: none;
            z-index: 20;
        }

        .animate-sparkle::before {
            left: -25px;
            animation: sparkle 2s ease-in-out infinite;
        }

        .animate-sparkle::after {
            right: -25px;
            animation: sparkle 2s ease-in-out infinite 0.5s;
        }

        .journey-btn {
            position: relative;
            overflow: hidden;
        }

        .journey-btn::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg,
                    transparent,
                    rgba(255, 255, 255, 0.1),
                    transparent);
            transform: rotate(45deg);
            transition: transform 0.6s;
        }

        .journey-btn:hover::before {
            transform: rotate(45deg) translate(50%, 50%);
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, {
                threshold: 0.1
            });

            document.querySelectorAll('.scroll-trigger').forEach((el) => observer.observe(el));
        });
    </script>
</head>

<body class="bg-gradient-to-br from-indigo-50 via-white to-purple-50 min-h-screen">
    <!-- Navigation Bar -->
    <nav class="bg-white backdrop-blur-md bg-opacity-90 sticky top-0 z-50 border-b border-purple-100 animate-pulse-border">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center space-x-2">
                    <span class="text-3xl font-extrabold bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent">
                        Youdemy
                    </span>
                    <span class="animate-bounce text-2xl">🚀</span>
                </div>

                <!-- Search Bar -->
                <div class="flex-1 max-w-2xl mx-8">
                    <div class="relative group">
                        <input type="text"
                            class="w-full px-4 py-2 rounded-full border-2 border-purple-200 focus:outline-none focus:border-purple-400 focus:ring-2 focus:ring-purple-200 transition-all duration-300 bg-purple-50 group-hover:bg-white"
                            placeholder="What do you want to learn today? 🤔">
                        <button class="absolute right-3 top-2 group-hover:scale-110 transition-transform">
                            <i class="fas fa-search text-purple-400"></i>
                        </button>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="flex items-center space-x-6">
                    <a href="#" class="text-gray-600 hover:text-purple-600 transition-colors duration-300 flex items-center space-x-1">
                        <i class="fas fa-compass"></i>
                        <span>Explore</span>
                    </a>
                    <a href="#" class="text-gray-600 hover:text-purple-600 transition-colors duration-300 flex items-center space-x-1">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>Teach</span>
                    </a>
                    <button class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-6 py-2 rounded-full hover:scale-105 transition-transform duration-300 shadow-lg hover:shadow-purple-200">
                        Join the Fun!
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-purple-100 to-indigo-100 transform -skew-y-6"></div>
        <div class="relative max-w-7xl mx-auto px-4 py-20">
            <div class="text-center hero-content">
                <div class="flex justify-center mb-6">
                    <img src="/api/placeholder/120/120" alt="Learning mascot" class="rounded-full border-4 border-white shadow-xl animate-float">
                </div>
                <h1 class="text-5xl font-bold mb-6 bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent">
                    Learning Just Got Awesome! 🎉
                </h1>
                <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
                    Join thousands of happy students who are already living their dreams.
                    Your next big adventure is just one click away! ✨
                </p>
                <div class="flex justify-center space-x-4">
                    <button class="group journey-btn animate-sparkle bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-8 py-4 rounded-full hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-purple-200 flex items-center space-x-2 relative">
                        <span class="relative z-10">Start Your Journey</span>
                        <i class="fas fa-arrow-right group-hover:animate-bounce-horizontal transition-transform relative z-10"></i>
                        <div class="absolute inset-0 bg-gradient-to-r from-purple-700 to-indigo-700 opacity-0 hover:opacity-100 transition-opacity duration-300"></div>
                    </button>
                    <button class="bg-white text-purple-600 px-8 py-4 rounded-full hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-purple-200 border-2 border-purple-200 flex items-center space-x-2">
                        <i class="fas fa-play"></i>
                        <span>Watch Demo</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Featured Categories -->
    <div class="max-w-7xl mx-auto px-4 py-16">
        <h2 class="text-3xl font-bold text-center mb-2 bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent scroll-trigger">
            Discover Your Passion
        </h2>
        <p class="text-center text-gray-600 mb-12">What magical skills will you learn today? 🌟</p>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Category Card -->
            <div class="bg-white rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 p-6 hover:-translate-y-2 group card-hover-effect scroll-trigger">
                <div class="card-icon transition-all duration-300 bg-purple-100 rounded-xl p-4 mb-4 w-16 h-16 flex items-center justify-center">
                    <i class="fas fa-code text-3xl text-purple-600"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Programming</h3>
                <p class="text-gray-600">Become a code wizard! 🧙‍♂️ Build amazing things from scratch.</p>
            </div>

            <!-- Business Card -->
            <div class="bg-white rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 p-6 hover:-translate-y-2 group card-hover-effect scroll-trigger">
                <div class="card-icon transition-all duration-300 bg-indigo-100 rounded-xl p-4 mb-4 w-16 h-16 flex items-center justify-center">
                    <i class="fas fa-chart-line text-3xl text-indigo-600"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Business</h3>
                <p class="text-gray-600">Turn your dreams into reality! 💫 Master entrepreneurship.</p>
            </div>

            <!-- Design Card -->
            <div class="bg-white rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 p-6 hover:-translate-y-2 group card-hover-effect scroll-trigger">
                <div class="card-icon transition-all duration-300 bg-pink-100 rounded-xl p-4 mb-4 w-16 h-16 flex items-center justify-center">
                    <i class="fas fa-palette text-3xl text-pink-600"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Design</h3>
                <p class="text-gray-600">Unleash your creativity! 🎨 Create stunning designs.</p>
            </div>

            <!-- Languages Card -->
            <div class="bg-white rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 p-6 hover:-translate-y-2 group card-hover-effect scroll-trigger">
                <div class="card-icon transition-all duration-300 bg-blue-100 rounded-xl p-4 mb-4 w-16 h-16 flex items-center justify-center">
                    <i class="fas fa-language text-3xl text-blue-600"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Languages</h3>
                <p class="text-gray-600">Speak to the world! 🌍 Master new languages.</p>
            </div>
        </div>
    </div>

    <!-- Featured Courses -->
    <div class="max-w-7xl mx-auto px-4 py-16">
        <h2 class="text-3xl font-bold text-center mb-2 bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent">
            Trending Courses
        </h2>
        <p class="text-center text-gray-600 mb-12">Join these popular learning adventures! 🚀</p>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Course Card -->
            <div class="bg-white rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 overflow-hidden group hover:-translate-y-2 scroll-trigger">
                <div class="relative">
                    <img src="/api/placeholder/400/200" alt="Course thumbnail" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-4 right-4 bg-white rounded-full px-3 py-1 text-sm font-semibold text-purple-600">
                        Best Seller 🔥
                    </div>
                </div>
                <div class="p-6">
                    <div class="flex items-center mb-2">
                        <span class="text-sm font-semibold text-purple-600">Programming</span>
                        <span class="mx-2">•</span>
                        <span class="text-sm text-gray-500 flex items-center">
                            <i class="fas fa-signal-alt mr-1"></i> Beginner
                        </span>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Web Development Mastery</h3>
                    <p class="text-gray-600 mb-4">Become a full-stack wizard! Master HTML, CSS, and JavaScript 🎨</p>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <img src="/api/placeholder/32/32" alt="Instructor" class="w-10 h-10 rounded-full border-2 border-purple-200">
                            <div>
                                <span class="block text-sm font-semibold">Sarah Johnson</span>
                                <span class="text-xs text-gray-500">Senior Developer</span>
                            </div>
                        </div>
                        <span class="text-lg font-bold text-purple-600">$49.99</span>
                    </div>
                </div>
            </div>

            <!-- More course cards... -->
        </div>
    </div>

    <!-- Fun Stats Section -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 py-16 mt-16 stats-section">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-white text-center">
                <div class="p-6">
                    <div class="text-4xl font-bold mb-2 stats-number">100K+</div>
                    <div class="text-purple-200">Happy Students 😊</div>
                </div>
                <div class="p-6">
                    <div class="text-4xl font-bold mb-2">1000+</div>
                    <div class="text-purple-200">Amazing Courses 📚</div>
                </div>
                <div class="p-6">
                    <div class="text-4xl font-bold mb-2">500+</div>
                    <div class="text-purple-200">Expert Teachers 🎓</div>
                </div>
                <div class="p-6">
                    <div class="text-4xl font-bold mb-2">4.9</div>
                    <div class="text-purple-200">Star Rating ⭐</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white">
        <div class="max-w-7xl mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4 text-purple-600">About Us</h3>
                    <p class="text-gray-600 mb-4">Making learning fun and accessible for everyone! 🌟</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-purple-600 hover:text-purple-800 text-xl hover:scale-110 transition-transform">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="#" class="text-purple-600 hover:text-purple-800 text-xl hover:scale-110 transition-transform">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-purple-600 hover:text-purple-800 text-xl hover:scale-110 transition-transform">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
                <!-- More footer content... -->
            </div>
        </div>
    </footer>

    <script>
        gsap.registerPlugin(ScrollTrigger);

        // Initial page load animation
        const tl = gsap.timeline();
        tl.from("nav", {
                y: -100,
                opacity: 0,
                duration: 1,
                ease: "power4.out"
            })
            .from(".hero-content > *", {
                y: 50,
                opacity: 0,
                duration: 1,
                stagger: 0.2
            }, "-=0.5")
            .from(".journey-btn", {
                scale: 0,
                rotation: -180,
                opacity: 0,
                duration: 0.8,
                ease: "back.out(1.7)"
            }, "-=0.5");

        // Floating animation for the mascot
        gsap.to(".animate-float", {
            y: -20,
            duration: 2,
            repeat: -1,
            yoyo: true,
            ease: "power1.inOut"
        });

        // Category cards stagger animation
        gsap.from(".card-hover-effect", {
            scrollTrigger: {
                trigger: ".card-hover-effect",
                start: "top 80%",
                toggleActions: "play none none none"
            },
            y: 50,
            opacity: 0,
            duration: 1,
            stagger: 0.2,
            ease: "power3.out"
        });

        // Course cards reveal animation
        gsap.utils.toArray('.scroll-trigger').forEach(element => {
            gsap.from(element, {
                scrollTrigger: {
                    trigger: element,
                    start: "top 80%",
                    toggleActions: "play none none none"
                },
                y: 30,
                opacity: 0,
                duration: 0.8,
                ease: "power2.out"
            });
        });

        // Stats counter animation
        const stats = document.querySelectorAll('.stats-number');
        stats.forEach(stat => {
            const value = stat.innerText;
            gsap.from(stat, {
                scrollTrigger: {
                    trigger: stat,
                    start: "top 80%",
                    toggleActions: "play none none none"
                },
                textContent: 0,
                duration: 2,
                snap: {
                    textContent: 1
                },
                stagger: 1,
                ease: "power1.inOut"
            });
        });

        // Parallax background effect
        gsap.to(".bg-gradient-to-br", {
            scrollTrigger: {
                trigger: "body",
                start: "top top",
                end: "bottom bottom",
                scrub: 1
            },
            backgroundPosition: "100% 100%",
            ease: "none"
        });

        // Button hover animation
        const button = document.querySelector('.journey-btn');

        button.addEventListener('mouseenter', () => {
            gsap.to(button, {
                scale: 1.05,
                duration: 0.3,
                ease: "power2.out"
            });

            gsap.to(button.querySelector('.fa-arrow-right'), {
                x: 5,
                duration: 0.3,
                repeat: -1,
                yoyo: true
            });

            gsap.to(button.querySelectorAll('.animate-sparkle::before, .animate-sparkle::after'), {
                scale: 1.2,
                opacity: 1,
                duration: 0.3,
                stagger: 0.1
            });
        });

        button.addEventListener('mouseleave', () => {
            gsap.to(button, {
                scale: 1,
                duration: 0.3
            });

            gsap.to(button.querySelector('.fa-arrow-right'), {
                x: 0,
                duration: 0.3
            });

            gsap.to(button.querySelectorAll('.animate-sparkle::before, .animate-sparkle::after'), {
                scale: 1,
                opacity: 0,
                duration: 0.3
            });
        });

        document.querySelectorAll('.card-hover-effect').forEach(card => {
            card.addEventListener('mouseenter', () => {
                gsap.to(card.querySelector('.card-icon'), {
                    y: -10,
                    scale: 1.1,
                    rotation: 10,
                    duration: 0.3,
                    ease: "power2.out"
                });
            });

            card.addEventListener('mouseleave', () => {
                gsap.to(card.querySelector('.card-icon'), {
                    y: 0,
                    scale: 1,
                    rotation: 0,
                    duration: 0.3,
                    ease: "power2.in"
                });
            });
        });

        // Footer reveal animation
        gsap.from("footer", {
            scrollTrigger: {
                trigger: "footer",
                start: "top 90%",
                toggleActions: "play none none none"
            },
            y: 50,
            opacity: 0,
            duration: 1
        });

        // Stats section parallax
        gsap.to(".stats-section", {
            scrollTrigger: {
                trigger: ".stats-section",
                start: "top bottom",
                end: "bottom top",
                scrub: 1
            },
            backgroundPosition: "center 30%",
            ease: "none"
        });
    </script>
</body>

</html>