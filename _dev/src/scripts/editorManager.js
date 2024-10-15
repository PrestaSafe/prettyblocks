import { createToaster } from '@meforma/vue-toaster'
const toaster = createToaster();
let numberOfEditors = 1;

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


    fetch(ajaxEditingUrl, {
        method: "POST",
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.number_of_editors) {
                // show alert if they are more than 1 editor and alert is enable
                numberOfEditors = data.number_of_editors;
                let alert_message = translate_alert_message.replace('{{ number }}',numberOfEditors);
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
            console.error('Error during fetch request: ', e);
        });
}

// Run first time
verifyConnectedEmployees();


// Run every 40 seconds
setInterval(verifyConnectedEmployees, 40000);