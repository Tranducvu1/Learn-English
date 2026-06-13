import { Helmet } from 'react-helmet-async';
import type { RouteSeo } from '../../config/seo';
import { SITE } from '../../config/site';
import { canonicalUrl } from '../../config/seo';

type Props = {
  seo: RouteSeo;
  noindex?: boolean;
};

export function SeoHead({ seo, noindex }: Props) {
  const url = canonicalUrl(seo.path);
  const keywords = seo.keywords.join(', ');

  const jsonLd = {
    '@context': 'https://schema.org',
    '@graph': [
      {
        '@type': 'WebSite',
        '@id': `${SITE.url}/#website`,
        url: SITE.url,
        name: SITE.nameFull,
        description: SITE.tagline,
        inLanguage: 'vi',
        potentialAction: {
          '@type': 'SearchAction',
          target: `${SITE.url}/tu-dien-tieng-trung?q={search_term_string}`,
          'query-input': 'required name=search_term_string',
        },
      },
      {
        '@type': 'EducationalOrganization',
        '@id': `${SITE.url}/#organization`,
        name: SITE.name,
        url: SITE.url,
        description: SITE.tagline,
        knowsAbout: ['Chinese language', 'HSK', 'Mandarin', '汉语', 'HSK考试'],
      },
      {
        '@type': 'WebPage',
        '@id': `${url}#webpage`,
        url,
        name: seo.title,
        description: seo.description,
        isPartOf: { '@id': `${SITE.url}/#website` },
        inLanguage: 'vi',
        breadcrumb: {
          '@type': 'BreadcrumbList',
          itemListElement: [
            { '@type': 'ListItem', position: 1, name: 'Trang chủ', item: canonicalUrl('/') },
            { '@type': 'ListItem', position: 2, name: seo.breadcrumb, item: url },
          ],
        },
      },
      {
        '@type': 'Course',
        name: 'Khóa học tiếng Trung & luyện thi HSK 1-6',
        description: 'Bài học HSK, từ vựng, flashcard SRS, luyện đề thi thử, video bài giảng',
        provider: { '@id': `${SITE.url}/#organization` },
        educationalLevel: 'Beginner to Advanced',
        inLanguage: ['vi', 'zh'],
        teaches: 'Chinese language (Mandarin)',
        isAccessibleForFree: true,
      },
    ],
  };

  return (
    <Helmet>
      <html lang={SITE.lang} />
      <title>{seo.title}</title>
      <meta name="description" content={seo.description} />
      <meta name="keywords" content={keywords} />
      <meta name="author" content={SITE.author} />
      <link rel="canonical" href={url} />
      {noindex && <meta name="robots" content="noindex,nofollow" />}
      {!noindex && <meta name="robots" content="index,follow,max-image-preview:large" />}
      <meta property="og:type" content="website" />
      <meta property="og:locale" content={SITE.locale} />
      <meta property="og:site_name" content={SITE.name} />
      <meta property="og:title" content={seo.title} />
      <meta property="og:description" content={seo.description} />
      <meta property="og:url" content={url} />
      <meta name="twitter:card" content="summary_large_image" />
      <meta name="twitter:title" content={seo.title} />
      <meta name="twitter:description" content={seo.description} />
      <script type="application/ld+json">{JSON.stringify(jsonLd)}</script>
    </Helmet>
  );
}
