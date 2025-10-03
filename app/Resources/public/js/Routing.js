/**
 * For now, we rely on the router.js script tag to be included
 * in the layout. This is just a helper module to get that object.
 */

const routes = require('../../../../web/js/fos_js_routes.json');
import Routing from '../../../../vendor/friendsofsymfony/jsrouting-bundle/Resources';

Routing.setRoutingData(routes);
