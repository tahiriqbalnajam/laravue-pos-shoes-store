import request from '@/utils/request';

export function getLastInoice(id) {
  return request({
    url: 'lastinvoiceid',
    method: 'get',
  });
}

export function getPreviousBalance(id) {
  return request({
    url: 'getpreviousbalance/' + id + '/1',
    method: 'get',
  });
}

export function getBadges(id) {
  return request({
    url: '/getbatchs/' + id,
    method: 'get',
  });
}
export function getpurLastInoice(id) {
  return request({
    url: 'lastpurinvoiceid',
    method: 'get',
  });
}
export function getwheatLastInoice(id) {
  return request({
    url: 'wheatlastinvoiceid/' + id,
    method: 'get',
  });
}

export function exchangeProducts(query) {
  return request({
    url: '/exchange_products',
    method: 'post',
    data: query,
  });
}
