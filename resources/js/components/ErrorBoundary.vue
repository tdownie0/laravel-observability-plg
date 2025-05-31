<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { ComponentPublicInstance, onErrorCaptured, ref, Ref } from 'vue';
import Button from './ui/button/Button.vue';

interface AuthUser {
    id: number | null;
}

interface Auth {
    user: AuthUser | null;
}

interface CustomPageProps {
    [key: string]: unknown;
    auth: Auth;
}

const error: Ref<Error | null> = ref(null);
const page = usePage<CustomPageProps>();

interface ErrorPayload {
    message: string;
    stack: string | undefined;
    componentName: string;
    lifecycleHook: string;
    userId: number | null;
    context: {
        userAgent: string;
        url: string;
        timestamp: string;
    };
}

const sendErrorToBackend = async (err: Error, instance: ComponentPublicInstance | null, info: string): Promise<void> => {
    const userId: number | null = page.props.auth.user ? page.props.auth.user.id : null;

    // Try to get the component name from the public instance
    let componentName = 'UnknownComponent';
    if (instance && (instance as any).$options && (instance as any).$options.name) {
        componentName = (instance as any).$options.name;
    }

    const payload: ErrorPayload = {
        message: err.message,
        stack: err.stack,
        componentName: componentName,
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
    } catch (axiosError: any) {
        // Use 'any' for axiosError as AxiosError type might require more specific imports
        if (axiosError.response) {
            console.error('Failed to send error to backend (Axios - Server Response Error):', axiosError.response.status, axiosError.response.data);
        } else if (axiosError.request) {
            console.error('Failed to send error to backend (Axios - No Response Error):', axiosError.request);
        } else {
            console.error('Failed to send error to backend (Axios - Request Setup Error):', axiosError.message);
        }
    }
};

onErrorCaptured((err: Error, instance: ComponentPublicInstance | null, info: string) => {
    console.log(err, instance, info);
    error.value = err;

    sendErrorToBackend(err, instance, info);

    return false;
});

const getErrorString = (err: Error | null): string => {
    if (!err) {
        return '';
    }
    // Convert the error object to a string, including non-enumerable properties like stack
    return JSON.stringify(err, Object.getOwnPropertyNames(err), 2);
};

const resetError = (): void => {
    error.value = null;
};
</script>

<template>
    <div v-if="error">
        <h1>You got an error:</h1>
        <pre style="white-space: pre-wrap"><code>{{ getErrorString(error) }}</code></pre>
        <Button @click="resetError" class="hover:cursor-pointer">Try Again</Button>
    </div>
    <slot v-else></slot>
</template>
