import './bootstrap';
import 'flowbite';

import Alpine from 'alpinejs';
import Sortable from 'sortablejs';
import { Scanner } from './components/scanner';

window.Alpine = Alpine;
window.Sortable = Sortable;
window.Scanner = Scanner;

Alpine.start();
