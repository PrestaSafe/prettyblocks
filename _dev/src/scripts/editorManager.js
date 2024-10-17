import { createToaster } from '@meforma/vue-toaster'
const toaster = createToaster();
let numberOfEditors = 1;
import { HttpClient } from '../services/HttpClient.js';
import { trans } from '../scripts/trans.js';
/**
 *
 * Checks the number of employees connected to the prettyblocks backoffice.
 * If there is more than one employee, an alert is displayed.
 *
 */
export function verifyConnectedEmployees() {

    let formData = new FormData();
    formData.append('action', 'employeeAlert');
    formData.append('session_id', sessionToken);
    formData.append('ajax', 1);

    if (typeof ajaxEditingUrl == "undefined") {
        console.error('Url is missing.')
        return;
    }

    HttpClient.post(ajaxEditingUrl, Object.fromEntries(formData))
        .then(data => {
            if (data.success && data.number_of_editors) {
                numberOfEditors = data.number_of_editors;
                let alert_message = trans('alert_message').replace('{{ number }}', numberOfEditors);
                if (numberOfEditors > 1) {
                    toaster.error(
                        alert_message,
                        {
                            position: "bottom",
                            duration: 5000,
                            max: 1,
                        }
                    );
                }
            } else {
                console.error('Error : ', data.error);
            }
        })
        .catch(e => {
            console.error('Error during HTTP request: ', e);
        });
}

// Run first time
verifyConnectedEmployees();


// Run every 40 seconds
setInterval(verifyConnectedEmployees, 40000);