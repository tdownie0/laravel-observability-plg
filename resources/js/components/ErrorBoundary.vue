<script setup>
import { usePage } from '@inertiajs/vue3';
import axios from 'axios'; 
import { onErrorCaptured, ref } from 'vue';

const error = ref(null);
const page = usePage();

const sendErrorToBackend = async (err, instance, info) => {
    const userId = page.props.auth.user ? page.props.auth.user.id : null;

    const payload = {
        message: err.message,
        stack: err.stack,
        componentName: instance && instance.type ? instance.type.__name || instance.type.name : 'UnknownComponent',
        lifecycleHook: info,
        userId: userId,
        context: {
            userAgent: navigator.userAgent,
            url: window.location.href,
            timestamp: new Date().toISOString(),
        },
    };

    console.log('Sending error to backend via Axios:', payload);

    try {
        const response = await axios.post('/api/log-client-error', payload);

        console.log('Error successfully sent to backend (Axios).', response.data);
    } catch (axiosError) {
        if (axiosError.response) {
            console.error('Failed to send error to backend (Axios - Server Response Error):', axiosError.response.status, axiosError.response.data);
        } else if (axiosError.request) {
            console.error('Failed to send error to backend (Axios - No Response Error):', axiosError.request);
        } else {
            console.error('Failed to send error to backend (Axios - Request Setup Error):', axiosError.message);
        }
    }
};

onErrorCaptured((err, instance, info) => {
    console.log(err, instance, info);
    error.value = err;

    sendErrorToBackend(err, instance, info);

    return false;
});

const getErrorString = (err) => JSON.stringify(err, Object.getOwnPropertyNames(err));

const resetError = () => {
    error.value = null;
};
</script>

<template>
    <div v-if="error">
        <h1>You got an error:</h1>
        <pre style="white-space: pre-wrap"><code>{{ getErrorString(error) }}</code></pre>
        <button @click="resetError" class="btn btn-primary">Try Again</button>
    </div>
    <slot v-else></slot>
</template>
