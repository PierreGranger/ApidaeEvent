import 'bootstrap'

import { faker, recaptchaKo, recaptchaOk, criteresInterdits } from './formulaire.js'
global.faker = faker
global.recaptchaKo = recaptchaKo
global.recaptchaOk = recaptchaOk
global.criteresInterdits = criteresInterdits

const $ = require('jquery');
global.$ = global.jQuery = $;


import 'bootstrap/dist/js/bootstrap.min.js'
import 'bootstrap/dist/css/bootstrap.min.css'

import 'bootstrap-chosen/dist/chosen.jquery-1.4.2/chosen.jquery.min.js'
import 'bootstrap-chosen/bootstrap-chosen.css'
import '@fortawesome/fontawesome-free/css/fontawesome.min.css'
import '@fortawesome/fontawesome-free/css/all.min.css'

import './formulaire.js'
import './formulaire.css'
