import { Datepicker } from 'vanillajs-datepicker'
import '/node_modules/vanillajs-datepicker/sass/datepicker.scss'

document.onreadystatechange = function () {
    if (document.readyState === "interactive" || document.readyState === "complete") {
        const elem = document.querySelector('input[data-exec="datepicker"]')
        if (elem != null) {
            const datepicker = new Datepicker(elem, {})
        }
    } else {
        throw "document.readyState unsupported value"
    }
}