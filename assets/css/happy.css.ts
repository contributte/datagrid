export const happyStyles = `/**
 * From happy-inputs by paveljanda:
 * https://github.com/paveljanda/happy/blob/94357b7146b5f3029cc565859a588c5832dd374a/src/happy.css
 */

.happy-color,
.happy-checkbox,
.happy-radio {
  color: #333333;
}

.happy-color > b,
.happy-checkbox > b,
.happy-radio > b {
  background-color: #333333;
}

.happy-color.active,
.active.happy-checkbox,
.active.happy-radio {
  color: #333333;
}

.happy-color.active > b,
.active.happy-checkbox > b,
.active.happy-radio > b {
  background-color: #333333;
}

.happy-color.primary,
.primary.happy-checkbox,
.primary.happy-radio {
  color: #333333;
}

.happy-color.primary > b,
.primary.happy-checkbox > b,
.primary.happy-radio > b {
  background-color: #333333;
}

.happy-color.primary.active,
.primary.active.happy-checkbox,
.primary.active.happy-radio {
  color: #4c86bb;
}

.happy-color.primary.active > b,
.primary.active.happy-checkbox > b,
.primary.active.happy-radio > b {
  background-color: #4c86bb;
}

.happy-color.success,
.success.happy-checkbox,
.success.happy-radio {
  color: #333333;
}

.happy-color.success > b,
.success.happy-checkbox > b,
.success.happy-radio > b {
  background-color: #333333;
}

.happy-color.success.active,
.success.active.happy-checkbox,
.success.active.happy-radio {
  color: #72b889;
}

.happy-color.success.active > b,
.success.active.happy-checkbox > b,
.success.active.happy-radio > b {
  background-color: #72b889;
}

.happy-color.info,
.info.happy-checkbox,
.info.happy-radio {
  color: #333333;
}

.happy-color.info > b,
.info.happy-checkbox > b,
.info.happy-radio > b {
  background-color: #333333;
}

.happy-color.info.active,
.info.active.happy-checkbox,
.info.active.happy-radio {
  color: #5bc0de;
}

.happy-color.info.active > b,
.info.active.happy-checkbox > b,
.info.active.happy-radio > b {
  background-color: #5bc0de;
}

.happy-color.warning,
.warning.happy-checkbox,
.warning.happy-radio {
  color: #333333;
}

.happy-color.warning > b,
.warning.happy-checkbox > b,
.warning.happy-radio > b {
  background-color: #333333;
}

.happy-color.warning.active,
.warning.active.happy-checkbox,
.warning.active.happy-radio {
  color: #f0bb65;
}

.happy-color.warning.active > b,
.warning.active.happy-checkbox > b,
.warning.active.happy-radio > b {
  background-color: #f0bb65;
}

.happy-color.danger,
.danger.happy-checkbox,
.danger.happy-radio {
  color: #333333;
}

.happy-color.danger > b,
.danger.happy-checkbox > b,
.danger.happy-radio > b {
  background-color: #333333;
}

.happy-color.danger.active,
.danger.active.happy-checkbox,
.danger.active.happy-radio {
  color: #ed6b6b;
}

.happy-color.danger.active > b,
.danger.active.happy-checkbox > b,
.danger.active.happy-radio > b {
  background-color: #ed6b6b;
}

.happy-color.white,
.white.happy-checkbox,
.white.happy-radio {
  color: #333333;
}

.happy-color.white > b,
.white.happy-checkbox > b,
.white.happy-radio > b {
  background-color: #333333;
}

.happy-color.white.active,
.white.active.happy-checkbox,
.white.active.happy-radio {
  color: #ffffff;
}

.happy-color.white.active > b,
.white.active.happy-checkbox > b,
.white.active.happy-radio > b {
  background-color: #ffffff;
}

.happy-border-color,
.happy-radio {
  border-color: rgba(51, 51, 51, 0.8);
}

.happy-border-color.active,
.active.happy-radio {
  border-color: #333333;
}

.happy-border-color.primary,
.primary.happy-radio {
  border-color: rgba(51, 51, 51, 0.8);
}

.happy-border-color.primary.active,
.primary.active.happy-radio {
  border-color: #4c86bb;
}

.happy-border-color.success,
.success.happy-radio {
  border-color: rgba(51, 51, 51, 0.8);
}

.happy-border-color.success.active,
.success.active.happy-radio {
  border-color: #72b889;
}

.happy-border-color.info,
.info.happy-radio {
  border-color: rgba(51, 51, 51, 0.8);
}

.happy-border-color.info.active,
.info.active.happy-radio {
  border-color: #5bc0de;
}

.happy-border-color.warning,
.warning.happy-radio {
  border-color: rgba(51, 51, 51, 0.8);
}

.happy-border-color.warning.active,
.warning.active.happy-radio {
  border-color: #f0bb65;
}

.happy-border-color.danger,
.danger.happy-radio {
  border-color: rgba(51, 51, 51, 0.8);
}

.happy-border-color.danger.active,
.danger.active.happy-radio {
  border-color: #ed6b6b;
}

.happy-border-color.white,
.white.happy-radio {
  border-color: rgba(51, 51, 51, 0.8);
}

.happy-border-color.white.active,
.white.active.happy-radio {
  border-color: #ffffff;
}

/**
 * Common
 */

input[type="radio"].happy,
input[type="checkbox"].happy {
  position: absolute;
  top: -50%;
  left: -50%;
  opacity: 0;
}

label:not(.selectable),
.noselect {
  -webkit-touch-callout: none;
  -webkit-user-select: none;
  -khtml-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}

label {
  cursor: pointer;
  position: relative;
}

/**
 * Checkbox
 */

.happy-checkbox {
  border-color: #333333;
  margin-right: 0.2em;
  position: relative;
  display: inline-block;
  line-height: 20px;
  vertical-align: middle;
  width: 16px;
  height: 16px;
  border-width: 2px;
  border-style: solid;
  cursor: pointer;
  box-sizing: border-box;
  top: -2px;
  -webkit-border-radius: 2.66667px;
  -moz-border-radius: 2.66667px;
  border-radius: 2.66667px;
}

.happy-checkbox svg {
  position: absolute;
  display: block;
  top: -2px;
  left: -2px;
  height: 16px;
  width: 16px;
  opacity: 0;
  -webkit-border-radius: 2.66667px;
  -moz-border-radius: 2.66667px;
  border-radius: 2.66667px;
  background-color: #333333;
  -ms-transform: scale(0.4);
  -webkit-transform: scale(0.4);
  transform: scale(0.4);
  -ms-transition: all 180ms;
  -webkit-transition: all 180ms;
  transition: all 180ms;
}

.happy-checkbox svg rect {
  fill: white;
}

.happy-checkbox svg rect:first-child {
  -ms-transform: rotate(45deg);
  -webkit-transform: rotate(45deg);
  transform: rotate(45deg);
}

.happy-checkbox svg rect:nth-child(2) {
  -ms-transform: rotate(-45deg);
  -webkit-transform: rotate(-45deg);
  transform: rotate(-45deg);
  /* fill: yellow; */
}

.happy-checkbox.thin {
  border-width: 1px;
}

.happy-checkbox.thin svg {
  top: -1px;
  left: -1px;
}

.happy-checkbox.white {
  border-color: #ffffff;
}

.happy-checkbox.gray-border {
  border-color: #858585;
}

.happy-checkbox.primary-border {
  border-color: #4c86bb;
}

.happy-checkbox.success-border {
  border-color: #72b889;
}

.happy-checkbox.info-border {
  border-color: #5bc0de;
}

.happy-checkbox.warning-border {
  border-color: #f0bb65;
}

.happy-checkbox.danger-border {
  border-color: #ed6b6b;
}

.happy-checkbox.primary svg {
  background-color: #4c86bb;
}

.happy-checkbox.success svg {
  background-color: #72b889;
}

.happy-checkbox.info svg {
  background-color: #5bc0de;
}

.happy-checkbox.warning svg {
  background-color: #f0bb65;
}

.happy-checkbox.danger svg {
  background-color: #ed6b6b;
}

.happy-checkbox.white svg {
  background-color: #ffffff;
}

.happy-checkbox.white svg rect {
  fill: #333333;
}

.happy-checkbox.active {
  border-color: transparent;
}

.happy-checkbox.active svg {
  opacity: 1;
  -ms-transform: scale(1);
  -webkit-transform: scale(1);
  transform: scale(1);
}

/**
 * Radio
 */

.happy-radio {
  position: relative;
  display: inline-block;
  line-height: 20px;
  vertical-align: middle;
  width: 16px;
  height: 16px;
  border-width: 2px;
  border-style: solid;
  cursor: pointer;
  box-sizing: border-box;
  top: -2px;
  -webkit-border-radius: 16px;
  -moz-border-radius: 16px;
  border-radius: 16px;
}

.happy-radio.thin {
  border-width: 1.66667px;
}

.happy-radio b {
  position: absolute;
  display: block;
  top: 2px;
  left: 2px;
  bottom: 2px;
  right: 2px;
  opacity: 0;
  -webkit-border-radius: 10.66667px;
  -moz-border-radius: 10.66667px;
  border-radius: 10.66667px;
  -ms-transform: scale(0.4);
  -webkit-transform: scale(0.4);
  transform: scale(0.4);
  -ms-transition: all 180ms;
  -webkit-transition: all 180ms;
  transition: all 180ms;
}

.happy-radio.active b {
  opacity: 1;
  -ms-transform: scale(1);
  -webkit-transform: scale(1);
  transform: scale(1);
}

.happy-radio.focus {
  outline: none;
  -webkit-box-shadow: 0px 0px 5px 0px rgba(50, 50, 50, 0.75);
  -moz-box-shadow: 0px 0px 5px 0px rgba(50, 50, 50, 0.75);
  box-shadow: 0px 0px 5px 0px rgba(50, 50, 50, 0.75);
}
`;
