import './bootstrap';
import '@fortawesome/fontawesome-free/css/all.min.css';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import './tts-reader';
import './click-define';
import './notebook';

window.Chart = Chart;
window.Alpine = Alpine;

Alpine.start();
