<?xml version="1.0" encoding="UTF-8"?>
<!--
    Sitemap XSL Stylesheet
    For Beautiful Display in Dynamic Mode
    
    Related Files:
    - core/class-builder.php
    - templates/sitemap-index.xsl
-->
<xsl:stylesheet version="2.0"
  xmlns:html="http://www.w3.org/TR/REC-html40"
  xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes" />

  <xsl:template match="/">
    <html lang="fa" dir="rtl">
      <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>سایت‌مپ XML</title>
        <style>
          * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
          }

          body {
          font-family: Tahoma, Arial, sans-serif;
          background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
          padding: 20px;
          direction: rtl;
          }

          .container {
          max-width: 1400px;
          margin: 0 auto;
          }

          .header {
          background: #fff;
          padding: 40px;
          border-radius: 12px 12px 0 0;
          box-shadow: 0 4px 6px rgba(0,0,0,0.1);
          }

          .header h1 {
          color: #2c3e50;
          font-size: 32px;
          margin-bottom: 10px;
          }

          .header p {
          color: #6c757d;
          font-size: 16px;
          margin-bottom: 20px;
          }

          .stats {
          display: flex;
          gap: 20px;
          margin-top: 20px;
          }

          .stat-box {
          background: #f5f7fa;
          padding: 15px 25px;
          border-radius: 8px;
          flex: 1;
          }

          .stat-label {
          color: #6c757d;
          font-size: 14px;
          margin-bottom: 5px;
          }

          .stat-value {
          color: #2c3e50;
          font-size: 24px;
          font-weight: bold;
          }

          .back-link {
          display: inline-block;
          margin-top: 20px;
          padding: 10px 20px;
          background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
          color: #fff;
          text-decoration: none;
          border-radius: 6px;
          font-weight: 600;
          transition: transform 0.15s ease;
          }

          .back-link:hover {
          transform: translateY(-2px);
          }

          .content {
          background: #fff;
          padding: 0;
          border-radius: 0 0 12px 12px;
          box-shadow: 0 4px 6px rgba(0,0,0,0.1);
          overflow: hidden;
          overflow-x: auto;
          }

          table {
          width: 100%;
          border-collapse: collapse;
          min-width: 800px;
          }

          thead {
          background: #f5f7fa;
          }

          thead th {
          padding: 15px 20px;
          text-align: right;
          font-weight: 600;
          color: #2c3e50;
          border-bottom: 2px solid #e9ecef;
          }

          tbody tr {
          border-bottom: 1px solid #e9ecef;
          transition: background 0.15s ease;
          }

          tbody tr:hover {
          background: #f8f9fa;
          }

          tbody td {
          padding: 15px 20px;
          color: #6c757d;
          }

          .url-cell {
          word-break: break-all;
          max-width: 600px;
          }

          .url-cell a {
          color: #667eea;
          text-decoration: none;
          }

          .url-cell a:hover {
          text-decoration: underline;
          }

          .priority-high {
          color: #46b450;
          font-weight: 600;
          }

          .priority-medium {
          color: #f0b849;
          font-weight: 600;
          }

          .priority-low {
          color: #999;
          font-weight: 600;
          }

          .row-number {
          color: #999;
          font-weight: 600;
          text-align: center;
          }

          @media (max-width: 768px) {
          .header h1 {
          font-size: 24px;
          }

          .stats {
          flex-direction: column;
          }

          table {
          font-size: 14px;
          }

          thead th,
          tbody td {
          padding: 10px;
          }
          }
        </style>
      </head>
      <body>
        <div class="container">
          <div class="header">
            <h1>🗺️ سایت‌مپ XML</h1>
            <p>فهرست تمام آدرس‌های این بخش از وب‌سایت</p>

            <div class="stats">
              <div class="stat-box">
                <div class="stat-label">تعداد URL ها</div>
                <div class="stat-value">
                  <xsl:value-of select="count(sitemap:urlset/sitemap:url)" />
                </div>
              </div>
              <div class="stat-box">
                <div class="stat-label">نوع</div>
                <div class="stat-value">XML URLset</div>
              </div>
            </div>

            <a href="sitemap.xml" class="back-link">← بازگشت به فهرست اصلی</a>
          </div>

          <div class="content">
            <table>
              <thead>
                <tr>
                  <th style="width: 50px;">#</th>
                  <th>آدرس صفحه</th>
                  <th style="width: 100px; text-align: center;">اولویت</th>
                  <th style="width: 120px; text-align: center;">تکرار</th>
                  <th style="width: 150px; text-align: center;">آخرین تغییر</th>
                </tr>
              </thead>
              <tbody>
                <xsl:for-each select="sitemap:urlset/sitemap:url">
                  <tr>
                    <td class="row-number">
                      <xsl:value-of select="position()" />
                    </td>
                    <td class="url-cell">
                      <a>
                        <xsl:attribute name="href">
                          <xsl:value-of select="sitemap:loc" />
                        </xsl:attribute>
                        <xsl:value-of select="sitemap:loc" />
                      </a>
                    </td>
                    <td style="text-align: center;">
                      <xsl:variable name="priority" select="sitemap:priority" />
                      <xsl:choose>
                        <xsl:when test="$priority &gt;= 0.8">
                          <span class="priority-high">
                            <xsl:value-of select="sitemap:priority" />
                          </span>
                        </xsl:when>
                        <xsl:when test="$priority &gt;= 0.5">
                          <span class="priority-medium">
                            <xsl:value-of select="sitemap:priority" />
                          </span>
                        </xsl:when>
                        <xsl:otherwise>
                          <span class="priority-low">
                            <xsl:value-of select="sitemap:priority" />
                          </span>
                        </xsl:otherwise>
                      </xsl:choose>
                    </td>
                    <td style="text-align: center;">
                      <xsl:choose>
                        <xsl:when test="sitemap:changefreq='always'">همیشه</xsl:when>
                        <xsl:when test="sitemap:changefreq='hourly'">ساعتی</xsl:when>
                        <xsl:when test="sitemap:changefreq='daily'">روزانه</xsl:when>
                        <xsl:when test="sitemap:changefreq='weekly'">هفتگی</xsl:when>
                        <xsl:when test="sitemap:changefreq='monthly'">ماهانه</xsl:when>
                        <xsl:when test="sitemap:changefreq='yearly'">سالانه</xsl:when>
                        <xsl:when test="sitemap:changefreq='never'">هرگز</xsl:when>
                        <xsl:otherwise>
                          <xsl:value-of select="sitemap:changefreq" />
                        </xsl:otherwise>
                      </xsl:choose>
                    </td>
                    <td style="text-align: center; direction: ltr;">
                      <xsl:value-of select="substring(sitemap:lastmod, 1, 10)" />
                    </td>
                  </tr>
                </xsl:for-each>
              </tbody>
            </table>
          </div>
        </div>
      </body>
    </html>
  </xsl:template>

</xsl:stylesheet>