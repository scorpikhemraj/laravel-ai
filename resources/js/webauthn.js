import {
    startRegistration,
    startAuthentication,
} from '@simplewebauthn/browser';
import axios from 'axios';

const checkSecureContext = () => {
    if (!window.isSecureContext) {
        alert('WebAuthn requires a secure context. Please access this site via HTTPS or http://localhost (not an IP address like 0.0.0.0).');
        return false;
    }
    return true;
};

export const registerPasskey = async (name = 'New Device') => {
    if (!checkSecureContext()) return false;

    try {
        const { data: options } = await axios.post('/auth/biometric/register/options');

        const attResp = await startRegistration({ optionsJSON: options });

        await axios.post('/auth/biometric/register', {
            ...attResp,
            name: name
        });

        alert('Passkey registered successfully!');
        return true;
    } catch (error) {
        console.error(error);
        alert('Registration failed: ' + (error.response?.data?.error || error.message));
        return false;
    }
};

export const authenticatePasskey = async (email = null) => {
    if (!checkSecureContext()) return false;

    try {
        const { data: options } = await axios.post('/login/biometric/options', { email });

        const asseResp = await startAuthentication({ optionsJSON: options });

        const { data: result } = await axios.post('/login/biometric', asseResp);

        if (result.success) {
            window.location.href = result.redirect;
        }
    } catch (error) {
        console.error(error);
        alert('Authentication failed: ' + (error.response?.data?.error || error.message));
    }
};
