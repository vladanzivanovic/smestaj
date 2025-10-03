const singleton = Symbol('PaginationMapper');

class PaginationMapper {
    constructor() {
        let Class = new.target;

        if (!Class[singleton]) {
            this.wrapper = tjq('.pagination');
            this.first = tjq('.first', this.wrapper);
            this.last = tjq('.last', this.wrapper);
            this.next = tjq('.next', this.wrapper);
            this.prev = tjq('.prev', this.wrapper);

            Class[singleton] = this;
        }

        return Class[singleton];
    }
}


export default PaginationMapper;