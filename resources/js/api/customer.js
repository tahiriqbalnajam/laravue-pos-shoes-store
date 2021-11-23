import request from '@/utils/request';

export function fetchListc(query) {
  return request({
    url: '/customer',
    method: 'get',
    params: query,
  });
}
export function search(query) {
  return request({
    url: '/search',
    method: 'get',
    params: { query },
  });
}
export function getKhata(query) {
  return request({
    url: '/get_khata_details',
    method: 'get',
    params: query,
  });
}
export function getKhataDate(query) {
  return request({
    url: '/get_khata_details_date',
    method: 'get',
    params: query,
  });
}
export function fetchAcc() {
  return request({
    url: '/get_accounts',
    method: 'get',
  });
}
export function getSaleman() {
  return request({
    url: '/get_saleman',
    method: 'get',
  });
}
