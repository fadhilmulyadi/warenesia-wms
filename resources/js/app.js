import './bootstrap';
import 'flowbite';

import Alpine from 'alpinejs';
import Sortable from 'sortablejs';
import { Scanner } from './components/scanner';
import { rackLocationField, productForm } from './modules/productForm';
import { itemsTable, submitFormWithValidation } from './modules/itemsTable';

window.Alpine = Alpine;
window.Sortable = Sortable;
window.Scanner = Scanner;
window.rackLocationField = rackLocationField;
window.productForm = productForm;
window.itemsTable = itemsTable;
window.submitFormWithValidation = submitFormWithValidation;

import staffDashboard from './modules/scanner/staffScanner.js';
import productIndexScanner from './modules/scanner/productIndexScanner.js';

Alpine.data('staffDashboard', staffDashboard);
Alpine.data('productIndexScanner', productIndexScanner);

Alpine.start();
