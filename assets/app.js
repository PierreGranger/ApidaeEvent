// Import Popper.js pour les tooltips Bootstrap
import '@popperjs/core'

import 'bootstrap'
import { Modal } from 'bootstrap';

const $ = require('jquery');
global.$ = global.jQuery = $;

const bootstrap = require('bootstrap');
global.bootstrap = bootstrap;
window.bootstrap = bootstrap;

import 'bootstrap/dist/css/bootstrap.min.css'
import 'select2/dist/css/select2.min.css'
import 'select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css'
import 'select2'

import { faker, recaptchaKo, recaptchaOk, criteresInterdits } from './formulaire.js'
global.faker = faker
global.recaptchaKo = recaptchaKo
global.recaptchaOk = recaptchaOk
global.criteresInterdits = criteresInterdits

import '@fortawesome/fontawesome-free/css/fontawesome.min.css'
import '@fortawesome/fontawesome-free/css/all.min.css'

import './formulaire.js'
import './formulaire.css'
