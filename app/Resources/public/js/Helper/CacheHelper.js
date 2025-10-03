const singleton = Symbol('CacheHelper');

class CacheHelper {
    constructor() {
        let Class = new.target;

        if(!Class[singleton]) {
            this.cache = {};

            Class[singleton] = this;
        }

        return Class[singleton];
    }

    add(key, value) {
        this.cache[key].push(value);
    }

    set(key, value) {
        this.cache[key] = value;
    }

    has(key) {
        return this.cache.hasOwnProperty(key);
    }

    get(key) {
        if (this.has(key)) {
            return this.cache[key];
        }

        return null;
    }
}

const cache = new CacheHelper();

export default cache;