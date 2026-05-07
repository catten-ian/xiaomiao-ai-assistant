const puppeteer = require('puppeteer');
const fs = require('fs');

const TOKEN = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiJkMmY4YjBjOC1lMTM1LTQ2NzctYTliNi0yZmI5MGQ5MzRmMWMiLCJ1c2VybmFtZSI6ImFkbWluIiwicm9sZSI6ImRpc3RyaWN0X2J1cmVhdSIsImlhdCI6MTc3ODE1OTgyMiwiZXhwIjoxNzc4NzY0NjIyfQ.D6YcnaQRMMN-rOkK9kXjcA9k2e_dN5jZ4uOLpAKpO1Y';

const wait = (ms) => new Promise(r => setTimeout(r, ms));

(async () => {
  const browser = await puppeteer.launch({
    headless: 'new',
    args: ['--no-sandbox', '--disable-gpu', '--disable-dev-shm-usage']
  });
  const page = await browser.newPage();
  await page.setViewport({ width: 1920, height: 1080 });
  
  await page.goto('http://localhost:8000/ai-review/records', { waitUntil: 'domcontentloaded' });
  await page.evaluate((token) => {
    localStorage.setItem('token', token);
  }, TOKEN);
  await page.goto('http://localhost:8000/ai-review/records', { waitUntil: 'networkidle0' });
  await wait(3000);
  
  const content = await page.evaluate(() => document.body.innerText);
  console.log('Page content preview:', content.substring(0, 400));
  
  await page.screenshot({ path: '/workspace/screenshots/records.png' });
  console.log('Saved:', fs.statSync('/workspace/screenshots/records.png').size, 'bytes');
  
  await browser.close();
})();
