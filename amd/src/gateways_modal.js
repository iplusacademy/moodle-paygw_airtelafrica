// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This module is responsible for Airtel Africa content in the gateways modal.
 *
 * @copyright  2023 Medical Access Uganda
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import * as Repository from './repository';
import Ajax from 'core/ajax';
import Config from 'core/config';
import Log from 'core/log';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Templates from 'core/templates';
import {get_string as getString} from 'core/str';

/**
 * Creates and shows a modal that contains a placeholder.
 *
 * @returns {Promise<Modal>}
 */
const showModalWithPlaceholder = async() => {
    const modal = await ModalFactory.create({
        body: await Templates.render('paygw_airtelafrica/placeholder', {})
    });
    modal.show();
    return modal;
};

/**
 * Process the payment.
 *
 * @param {string} component Name of the component that the itemId belongs to
 * @param {string} paymentArea The area of the component that the itemId belongs to
 * @param {number} itemId An internal identifier that is used by the component
 * @param {string} description Description of the payment
 * @returns {Promise<string>}
 */
export const process = (component, paymentArea, itemId, description) => {
    return Promise.all([
        showModalWithPlaceholder(),
        Repository.getConfigForJs(component, paymentArea, itemId),
    ])
    .then(([modal, airtelConfig]) => {
        modal.setTitle(getString('pluginname', 'paygw_airtelafrica'));
        console.log(description);  // eslint-disable-line
        const phoneNumber = modal.getRoot().find('#airtel-phone');
        phoneNumber.append('<h4>' + airtelConfig.phone + '</h4>');
        const userCountry = modal.getRoot().find('#airtel-country');
        userCountry.append('<h4>' + airtelConfig.usercountry + '</h4>');
        const extraDiv = modal.getRoot().find('#airtel-extra');
        extraDiv.append('<h4>' + airtelConfig.cost + ' ' + airtelConfig.currency + '</h4>');
        modal.getRoot().on(ModalEvents.hidden, () => {
            // Destroy when hidden.
            console.log('Destroy modal');    // eslint-disable-line
            modal.destroy();
        });

        return Promise.all([modal, airtelConfig]);
    })
    .then(([modal, airtelConfig]) => {
        var cancelButton = modal.getRoot().find('#airtel-cancel');
        cancelButton.on('click', function() {
            modal.destroy();
        });
        var payButton = modal.getRoot().find('#airtel-pay');
        payButton.on('click', function(e) {
            e.preventDefault();
            Promise.all([
                Repository.transactionStart(component, paymentArea, itemId),
            ])
            .then(([airtelPay]) => {
                const transId = airtelPay.transactionid;
                modal.setBody(Templates.render('paygw_airtelafrica/busy', {
                    "sesskey": Config.sesskey,
                    "phone": airtelConfig.phone,
                    "country": airtelConfig.country,
                    "component": component,
                    "paymentarea": paymentArea,
                    "transactionid": transId,
                    "itemid": itemId,
                    "description": description,
                    "reference": airtelConfig.reference,
                }));
                cancelButton = modal.getRoot().find('#airtel-cancel');
                cancelButton.on('click', function() {
                    e.preventDefault();
                    modal.destroy();
                });
                payButton = modal.getRoot().find('#airtel-pay');
                payButton.on('click', function() {
                    e.preventDefault();
                });
                console.log('Airtel Africa payment process started');  // eslint-disable-line
                console.log('Transaction id: ' + transId);  // eslint-disable-line
                var arrayints = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
                var interval = airtelConfig.timeout;
                var cont = true;
                arrayints.forEach(function(el, index) {
                    setTimeout(function() {
                        if (cont == true) {
                            const progressDiv = modal.getRoot().find('#airtel-progress_bar');
                            progressDiv.attr('value', el * 10);
                            if (transId != '') {
                                modal.setFooter(el + '/10');
                                Ajax.call([{
                                    methodname: "paygw_airtelafrica_transaction_complete",
                                    args: {
                                        component,
                                        paymentarea: paymentArea,
                                        itemid: itemId,
                                        transactionid: transId,
                                    },
                                    done: function(airtelPing) {
                                        console.log(el + '/10 ' + airtelPing.message);  // eslint-disable-line
                                        if (airtelPing.message == 'Transaction Failed') {
                                            cont = false;
                                            const a = '<br/><div class="p-3 mb-2 bg-danger text-white font-weight-bold">';
                                            modal.setBody(a + airtelPing.message + '</div>');
                                            modal.setFooter('Transaction with id '+ transId + ' failed');
                                        }
                                        if (airtelPing.success == true) {
                                            modal.setFooter('Transaction with id '+ transId + ' succeeded');
                                            cont = false;
                                            const spinnerDiv = modal.getRoot().find('#airtel-spinner');
                                            const a = '<br/><div class="p-3 mb-2 text-success font-weight-bold">';
                                            const outDiv = modal.getRoot().find('#airtel-out');
                                            outDiv.append(a + airtelPing.message + '</div>');
                                            spinnerDiv.attr('style', 'display: none;');
                                            cancelButton.attr('style', 'display: none;');
                                            const payButton1 = modal.getRoot().find('#airtel-pay');
                                            payButton1.on('click', function() {
                                                const loc = window.location.href;
                                                window.location.replace(loc);
                                            });
                                        }
                                    },
                                    fail: function(e) {
                                        console.log('Airtel Africa payment failed');  // eslint-disable-line
                                        Log.debug(e);
                                    }
                                }]);

                                if (el > 9) {
                                    modal.destroy();
                                }
                            }
                        }
                    }, index * interval);
                });
                return new Promise(() => null);
            }).catch(e => {
                // We want to use promise reject here - as that's what core payment stuff expects.
                modal.setBody(getString('unable', 'paygw_airtelafrica'));
                console.log('Airtel Africa payment rejected');  // eslint-disable-line
                Log.debug('Airtel Africa payment rejected');
                Log.debug(e);
            });
        });
        return new Promise(() => null);
    }).catch(e => {
        Log.debug('Global error.');
        Log.debug(e);
        return Promise.reject(e.message);
    });
};
