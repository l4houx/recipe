import './bootstrap.js';

import 'bootstrap/dist/css/bootstrap.min.css';

/*
import canvasConfetti from 'canvas-confetti';

document.body.addEventListener('click', () => {
    canvasConfetti()
})
*/

import './vendor/darkMode.js';
import './js/theme.min.js';

/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/css/app.min.css'

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
