class OrderSinglePageMapper {
    constructor() {
        this.postAuthBtn = '#post_auth_btn';
        this.refundBtn = '#refund_btn';
        this.voidBtn = '#void_btn';

        if (!OrderSinglePageMapper.instance) {
            OrderSinglePageMapper.instance = this;
        }

        return OrderSinglePageMapper.instance;
    }
}

const orderSinglePageMapper = new OrderSinglePageMapper();

Object.freeze(orderSinglePageMapper);

export default orderSinglePageMapper;