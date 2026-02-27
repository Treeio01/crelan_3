import express from 'express';
import axios from 'axios';
const app = express();
const port = 3000;

// Домен, от имени которого делаем запросы к целевому серверу
const myOrigin = 'https://idp.prd.itsme.services';

// Разрешаем CORS для всех запросов (или укажите конкретный домен)
app.use((req, res, next) => {
  // Разрешаем запросы с любого origin (для разработки)
  // В продакшене лучше указать конкретный домен: res.setHeader('Access-Control-Allow-Origin', 'https://idp.prd.itsme.services');
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');
  
  if (req.method === 'OPTIONS') {
    return res.sendStatus(200);
  }
  next();
});

// Роут для проксирования запросов на SVG
app.get('/proxy/:logoId', async (req, res) => {
  const logoId = req.params.logoId;
  const url = `https://assets.prd.itsme.services/poka-yoke/Logo_${logoId}.svg`;

  // Логируем входящий запрос
  console.log('\n=== ВХОДЯЩИЙ ЗАПРОС ===');
  console.log('URL:', req.url);
  console.log('Method:', req.method);
  console.log('Headers:', JSON.stringify(req.headers, null, 2));
  console.log('LogoId:', logoId);
  console.log('Target URL:', url);

  try {
    // Заголовки точно как в оригинальном браузерном запросе
    const requestHeaders = {
      'accept': 'image/xml+svg',
      'referer': `${myOrigin}/`,
      'sec-ch-ua': '"Google Chrome";v="143", "Chromium";v="143", "Not A(Brand";v="24"',
      'sec-ch-ua-mobile': '?0',
      'sec-ch-ua-platform': '"macOS"',
      'user-agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36',
    };

    console.log('\n=== ИСХОДЯЩИЙ ЗАПРОС ===');
    console.log('URL:', url);
    console.log('Method: GET');
    console.log('Headers:', JSON.stringify(requestHeaders, null, 2));

    const response = await axios.get(url, {
      responseType: 'text',
      headers: requestHeaders,
    });
    
    console.log('\n=== ОТВЕТ ОТ СЕРВЕРА ===');
    console.log('Status:', response.status, response.statusText);
    console.log('Headers:', JSON.stringify(response.headers, null, 2));
    console.log('Content-Type:', response.headers['content-type']);
    console.log('Content-Length:', response.headers['content-length']);
    console.log('Data length:', response.data?.length || 0);
    
    // Устанавливаем CORS заголовки и Content-Type в ответе клиенту
    res.set('Content-Type', response.headers['content-type'] || 'image/svg+xml');
    res.set('Access-Control-Allow-Origin', '*'); // Или конкретный домен
    res.send(response.data);
    
    console.log('\n=== ОТВЕТ КЛИЕНТУ ОТПРАВЛЕН ===\n');
  } catch (error) {
    console.error('\n=== ОШИБКА ===');
    console.error('Message:', error.message);
    console.error('Code:', error.code);
    
    if (error.response) {
      console.error('Status:', error.response.status);
      console.error('Status Text:', error.response.statusText);
      console.error('Response Headers:', JSON.stringify(error.response.headers, null, 2));
      console.error('Response Data:', error.response.data?.substring(0, 500));
    } else if (error.request) {
      console.error('Request made but no response received');
      console.error('Request:', error.request);
    }
    console.error('==================\n');
    
    res.status(error.response?.status || 500).send('Ошибка при загрузке SVG');
  }
});

app.listen(port, () => {
  console.log(`Proxy server running at http://localhost:${port}`);
});
