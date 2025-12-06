import './bootstrap';
import 'flowbite';

import Alpine from 'alpinejs';
import Sortable from 'sortablejs';
import { Scanner } from './components/scanner';

window.Alpine = Alpine;
window.Sortable = Sortable;
window.Scanner = Scanner;

import staffDashboard from './modules/scanner/staffScanner.js';
import productIndexScanner from './modules/scanner/productIndexScanner.js';

Alpine.data('staffDashboard', staffDashboard);
Alpine.data('productIndexScanner', productIndexScanner);

Alpine.start();
