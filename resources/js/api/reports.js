import request from '@/utils/request';

export function getStockValue() {
  return request({
    url: '/stock_value_report',
    method: 'get',
  });
}

export function getStockRetailValue() {
  return request({
    url: '/stock_retial_value_report',
    method: 'get',
  });
}
