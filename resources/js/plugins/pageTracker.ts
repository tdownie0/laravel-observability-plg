import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { App } from 'vue';

declare global {
    interface Window {
        AuthUser?: { id: number | null };
    }
}

interface PageTrackerOptions {
    apiEndpoint: string;
}

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

export const pageTrackerPlugin = {
    install(app: App, options?: PageTrackerOptions) {
        const apiEndpoint = options?.apiEndpoint || '/api/track-page-visit';

        let sessionId = localStorage.getItem('user_session_id');
        if (!sessionId) {
            sessionId = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
            localStorage.setItem('user_session_id', sessionId);
        }

        let previousUrl: string | null = null; // Store the previous URL

        router.on('before', (event) => {
            if (event.detail.visit.url) {
                previousUrl = window.location.href;
            }
        });

        router.on('navigate', () => {
            setTimeout(() => {
                const page = usePage<CustomPageProps>();
                const props = page.props;
                const userId: number | null = props.auth?.user ? props.auth.user.id : null;
                const url = window.location.pathname + window.location.search;
                const referrer = previousUrl || document.referrer;

                const currentDocumentTitle = document.title;

                axios
                    .post(apiEndpoint, {
                        user_id: userId,
                        session_id: sessionId,
                        page_url: url,
                        page_title: currentDocumentTitle,
                        referrer: referrer,
                        browser_info: navigator.userAgent,
                        screen_resolution: `${window.screen.width}x${window.screen.height}`,
                    })
                    .catch((error) => {
                        console.error('Error tracking page visit:', error);
                    });
            }, 10);
        });
    },
};
