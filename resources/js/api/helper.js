import request from '@/utils/request';

export function getDashboardTop() {
  return request({
    url: '/dashboardtop',
    method: 'get',
  });
}

export function getAreas() {
  return request({
    url: '/get_areas',
    method: 'get',
  });
}
export function dailySale() {
  return request({
    url: '/daily_sale_linechart',
    method: 'get',
  });
}
export function totalAccounts() {
  return request({
    url: '/total_accounts',
    method: 'get',
  });
}
export function dailystock() {
  return request({
    url: '/stock_data_dashboard',
    method: 'get',
  });
}

