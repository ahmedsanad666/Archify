# Phase 3 — Public Shell + Static Content

خلصنا مرحلة الـ public shell في Archify — يعني الموقع العام بقى يشتغل بثلاث لغات من الـ URL، مش بس الـ admin.

المرحلة دي غطّت الـ locale routing (`/` للإنجليزي، `/tr` و `/ar` للباقي)، الـ Navbar/Footer مع language switcher، وصفحات Home / About / Team / FAQ مربوطة بـ Services و Resources من الـ backend.

## التحدي التقني اللي ناس كتير بتغلط فيه

في Inertia + Laravel، لو حطيت `{locale?}` كـ optional prefix على الراوتس، Laravel ممكن يفهم `/about` إن الـ locale اسمه `about` — فتفشل الصفحة بـ 404 أو تخلط المسارات. الحل اللي استخدمناه: راوتس **named** بدون prefix للغة الافتراضية، ونسخة **unnamed** تحت `/tr` و `/ar`، والـ Vue (`useLocale`) يضيف الـ prefix لما اللغة مش default.

غلط تاني شائع: `HandleInertiaRequests` بيحسب الـ `locale` **قبل** ما `SetLocale` يشتغل لو القيم مش lazy closures. النتيجة؟ تفتح `/tr` والـ UI لسه إنجليزي. خلّينا `locale` و `ui` و `siteSettings` تتحسب بعد الـ middleware stack.

## ليه كده مش SSR من دلوقتي؟

الـ public shell دلوقتي بيجيب CMS content من الـ DB (sliders, about, projects…) والـ static chrome من `lang/en|tr|ar.json`. الـ DeepL لسه للـ CMS tabs في الأدمن بس — مش لترجمة أزرار الـ navbar. ده فصل مقصود عشان الـ shared hosting والـ jobs.

## الأدوات والأنماط

Repository → Service → thin Controller → Inertia Resource  
`SetLocale` middleware، `useLocale` + `useUiTranslations`، ومكوّنات Public زي `HeroSlider` و `ProjectCard` متوافقة مع الـ dark cork design system وـ RTL (`ms`/`me`/`start`/`end`).

## الجاية

Phase 4: صفحات Services و Projects العامة بالـ locale-aware slugs.

— أحمد سند، Software Engineer  
بتابع الرحلة في بناء CMS معماري multilingual من الصفر.

#Laravel #InertiaJS #Vue3 #i18n #Multilingual #Architecture #WebDevelopment #SoftwareEngineering
