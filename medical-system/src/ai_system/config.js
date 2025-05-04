// API配置

// 判断当前运行环境
const isDev = import.meta.env.MODE === 'development';

// 配置API基础URL
// 开发环境使用localhost:8000，生产环境使用相对路径
export const API_BASE_URL = isDev ? 'http://localhost:8000' : '';

// WebSocket配置
export const getWebSocketUrl = () => {
  const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
  
  // 开发环境固定使用localhost:8000
  if (isDev) {
    return `${protocol}//localhost:8000/ws/chat`;
  }
  
  // 生产环境使用相对路径
  return `${protocol}//${window.location.host}/ws/chat`;
};

// API路径
export const API_PATHS = {
  CHAT: '/chat',
  SESSIONS: '/sessions',
  SESSION: (id) => `/session/${id}`
};