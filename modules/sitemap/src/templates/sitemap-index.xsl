<?xml version="1.0" encoding="UTF-8"?>
<!--
    Sitemap Index XSL Stylesheet
    For Beautiful Display of Main Index in Dynamic Mode
    
    Related Files:
    - core/class-builder.php
    - templates/sitemap.xsl
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
                <meta name="robots" content="noindex, follow" />
                <title>فهرست سایت‌مپ XML</title>
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
                    max-width: 1200px;
                    margin: 0 auto;
                    }

                    .header {
                    background: #fff;
                    padding: 40px;
                    border-radius: 12px 12px 0 0;
                    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                    text-align: center;
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
                    display: inline-flex;
                    gap: 10px;
                    align-items: center;
                    background: #f5f7fa;
                    padding: 12px 25px;
                    border-radius: 8px;
                    }

                    .stats-label {
                    color: #6c757d;
                    font-size: 14px;
                    }

                    .stats-value {
                    color: #667eea;
                    font-size: 24px;
                    font-weight: bold;
                    margin: 0 5px;
                    }

                    .badge {
                    display: inline-block;
                    padding: 4px 12px;
                    background: #667eea;
                    color: white;
                    border-radius: 12px;
                    font-size: 11px;
                    font-weight: 600;
                    }

                    .content {
                    background: #fff;
                    padding: 0;
                    border-radius: 0 0 12px 12px;
                    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                    overflow: hidden;
                    }

                    table {
                    width: 100%;
                    border-collapse: collapse;
                    }

                    thead {
                    background: #f5f7fa;
                    }

                    thead th {
                    padding: 18px 30px;
                    text-align: right;
                    font-weight: 600;
                    color: #2c3e50;
                    border-bottom: 2px solid #e9ecef;
                    font-size: 14px;
                    }

                    tbody tr {
                    border-bottom: 1px solid #e9ecef;
                    transition: background 0.15s ease;
                    }

                    tbody tr:hover {
                    background: #f8f9fa;
                    }

                    tbody td {
                    padding: 20px 30px;
                    color: #6c757d;
                    }

                    .sitemap-link {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    color: #667eea;
                    text-decoration: none;
                    font-weight: 500;
                    font-size: 16px;
                    transition: all 0.15s ease;
                    }

                    .sitemap-link:hover {
                    color: #764ba2;
                    }

                    .sitemap-icon {
                    font-size: 24px;
                    opacity: 0.8;
                    }

                    .sitemap-name {
                    flex: 1;
                    }

                    .url-preview {
                    font-size: 12px;
                    color: #adb5bd;
                    font-family: 'Courier New', monospace;
                    margin-top: 6px;
                    direction: ltr;
                    text-align: left;
                    word-break: break-all;
                    }

                    .lastmod {
                    color: #6c757d;
                    font-size: 14px;
                    white-space: nowrap;
                    direction: ltr;
                    text-align: center;
                    }

                    .footer {
                    margin-top: 30px;
                    text-align: center;
                    color: rgba(255,255,255,0.9);
                    font-size: 14px;
                    }

                    @media (max-width: 768px) {
                    .header h1 {
                    font-size: 24px;
                    }

                    thead th,
                    tbody td {
                    padding: 12px 15px;
                    }

                    .sitemap-link {
                    font-size: 14px;
                    }

                    .url-preview {
                    display: none;
                    }
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>🗺️ فهرست سایت‌مپ XML</h1>
                        <p>این فایل شامل لینک‌های تمام بخش‌های سایت است</p>

                        <div class="stats">
                            <span class="stats-label">تعداد سایت‌مپ‌ها:</span>
                            <span class="stats-value">
                                <xsl:value-of select="count(sitemap:sitemapindex/sitemap:sitemap)" />
                            </span>
                            <span class="badge">XML Sitemap Index</span>
                        </div>
                    </div>

                    <div class="content">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 65%;">نام سایت‌مپ</th>
                                    <th style="width: 35%; text-align: center;">آخرین بروزرسانی</th>
                                </tr>
                            </thead>
                            <tbody>
                                <xsl:for-each select="sitemap:sitemapindex/sitemap:sitemap">
                                    <tr>
                                        <td>
                                            <a class="sitemap-link">
                                                <xsl:attribute name="href">
                                                    <xsl:value-of select="sitemap:loc" />
                                                </xsl:attribute>
                                                <span class="sitemap-icon">
                                                    <xsl:call-template name="get-icon">
                                                        <xsl:with-param name="url"
                                                            select="sitemap:loc" />
                                                    </xsl:call-template>
                                                </span>
                                                <span class="sitemap-name">
                                                    <xsl:call-template name="get-name">
                                                        <xsl:with-param name="url"
                                                            select="sitemap:loc" />
                                                    </xsl:call-template>
                                                    <div class="url-preview">
                                                        <xsl:value-of select="sitemap:loc" />
                                                    </div>
                                                </span>
                                            </a>
                                        </td>
                                        <td class="lastmod">
                                            <xsl:value-of select="substring(sitemap:lastmod, 1, 10)" />
                                        </td>
                                    </tr>
                                </xsl:for-each>
                            </tbody>
                        </table>
                    </div>

                    <div class="footer">
                        ساخته شده با ابزار سایت‌مپ YJ19
                    </div>
                </div>
            </body>
        </html>
    </xsl:template>

    <!-- Get Icon Based on URL -->
    <xsl:template name="get-icon">
        <xsl:param name="url" />
        <xsl:choose>
            <xsl:when test="contains($url, 'sitemap-homepage')">🏠</xsl:when>
            <xsl:when test="contains($url, 'sitemap-post.xml')">📝</xsl:when>
            <xsl:when test="contains($url, 'sitemap-page.xml')">📄</xsl:when>
            <xsl:when test="contains($url, 'sitemap-product.xml')">🛒</xsl:when>
            <xsl:when test="contains($url, 'sitemap-category')">📁</xsl:when>
            <xsl:when test="contains($url, 'sitemap-post_tag')">🏷️</xsl:when>
            <xsl:when test="contains($url, 'sitemap-product_cat')">📁</xsl:when>
            <xsl:when test="contains($url, 'sitemap-product_tag')">🏷️</xsl:when>
            <xsl:when test="contains($url, 'sitemap-authors')">👤</xsl:when>
            <xsl:when test="contains($url, 'sitemap-dates')">📅</xsl:when>
            <xsl:otherwise>📌</xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <!-- Get Persian Name Based on URL -->
    <xsl:template name="get-name">
        <xsl:param name="url" />
        <xsl:choose>
            <xsl:when test="contains($url, 'sitemap-homepage')">صفحه اصلی</xsl:when>
            <xsl:when test="contains($url, 'sitemap-post.xml')">نوشته‌ها</xsl:when>
            <xsl:when test="contains($url, 'sitemap-page.xml')">صفحات</xsl:when>
            <xsl:when test="contains($url, 'sitemap-product.xml')">محصولات</xsl:when>
            <xsl:when test="contains($url, 'sitemap-category')">دسته‌بندی‌ها</xsl:when>
            <xsl:when test="contains($url, 'sitemap-post_tag')">برچسب‌ها</xsl:when>
            <xsl:when test="contains($url, 'sitemap-product_cat')">دسته محصولات</xsl:when>
            <xsl:when test="contains($url, 'sitemap-product_tag')">برچسب محصولات</xsl:when>
            <xsl:when test="contains($url, 'sitemap-authors')">نویسندگان</xsl:when>
            <xsl:when test="contains($url, 'sitemap-dates')">آرشیو تاریخی</xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="substring-after(substring-before($url, '.xml'), 'sitemap-')" />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

</xsl:stylesheet>