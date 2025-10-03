import * as Sentry from '@sentry/browser';
import { Integrations } from "@sentry/tracing";

Sentry.init({
    dsn: SENTRY_DSN,
    environment: APP_ENV,
    integrations: [new Integrations.BrowserTracing()],
    tracesSampleRate: 1.0,
});