<?php
/**
 * Happy FAQs pattern definitions.
 *
 * Each entry: title, description, group (general, dynamic, store, industry),
 * keywords and block content built with FAQ_Woocommerce_Patterns helpers.
 * An optional preview entry replaces content in the gallery preview.
 *
 * @package FAQ_Woocommerce
 * @since   3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$p = 'FAQ_Woocommerce_Patterns';

$general_faqs = array(
    array( __( 'How do I get in touch with you?', 'faq-for-woocommerce' ), __( 'Send us a message through the contact form or email us. We reply within one business day, usually much sooner.', 'faq-for-woocommerce' ) ),
    array( __( 'What are your opening hours?', 'faq-for-woocommerce' ), __( 'We are available Monday to Friday, 9am to 6pm. Messages sent outside these hours are answered the next working day.', 'faq-for-woocommerce' ) ),
    array( __( 'Where are you located?', 'faq-for-woocommerce' ), __( 'Our team works from our main office and remotely. Visits are by appointment, so please contact us before you come by.', 'faq-for-woocommerce' ) ),
    array( __( 'Do you offer refunds?', 'faq-for-woocommerce' ), __( 'Yes. If something is not right, contact us within 30 days and we will make it right or refund you in full.', 'faq-for-woocommerce' ) ),
    array( __( 'How is my personal data used?', 'faq-for-woocommerce' ), __( 'Only to provide our service and reply to you. We never sell your data. Our privacy policy explains the details.', 'faq-for-woocommerce' ) ),
);

return array(

    /* ---------------------------------------------------------------- General */

    'centered'          => array(
        'title'       => __( 'Centered FAQ', 'faq-for-woocommerce' ),
        'description' => __( 'A calm, centered section with a heading, intro and soft cards.', 'faq-for-woocommerce' ),
        'group'       => 'general',
        'keywords'    => array( 'faq', 'centered', 'cards' ),
        'content'     => $p::section(
            $p::eyebrow( esc_html__( 'FAQ', 'faq-for-woocommerce' ), '#4f46e5' )
            . $p::heading( esc_html__( 'Frequently asked questions', 'faq-for-woocommerce' ) )
            . $p::lead( esc_html__( 'Everything you need to know. Can’t find an answer? Our team is happy to help.', 'faq-for-woocommerce' ) )
            . $p::custom_faqs( $general_faqs, 'cards' )
        ),
    ),

    'two-column'        => array(
        'title'       => __( 'Two-column FAQ', 'faq-for-woocommerce' ),
        'description' => __( 'Heading and contact button on the left, questions on the right.', 'faq-for-woocommerce' ),
        'group'       => 'general',
        'keywords'    => array( 'faq', 'columns', 'split' ),
        'content'     => $p::section(
            $p::columns(
                $p::eyebrow( esc_html__( 'Support', 'faq-for-woocommerce' ), '#0f766e', 'left' )
                . $p::heading( esc_html__( 'Questions, answered', 'faq-for-woocommerce' ), 'left', 'clamp(28px, 3.4vw, 38px)' )
                . $p::lead( esc_html__( 'Quick answers to the things people ask us most.', 'faq-for-woocommerce' ), 'left', '28px' )
                . $p::button( esc_html__( 'Contact us', 'faq-for-woocommerce' ) ),
                $p::custom_faqs( $general_faqs, 'minimal' )
            ),
            '#ffffff',
            '1120px'
        ),
    ),

    'contact-card'      => array(
        'title'       => __( 'FAQ with contact card', 'faq-for-woocommerce' ),
        'description' => __( 'Questions followed by a friendly “Still have questions?” card.', 'faq-for-woocommerce' ),
        'group'       => 'general',
        'keywords'    => array( 'faq', 'contact', 'cta' ),
        'content'     => $p::section(
            $p::eyebrow( esc_html__( 'Help', 'faq-for-woocommerce' ), '#be123c' )
            . $p::heading( esc_html__( 'Good to know', 'faq-for-woocommerce' ) )
            . $p::lead( esc_html__( 'Short answers to common questions.', 'faq-for-woocommerce' ) )
            . $p::custom_faqs( array_slice( $general_faqs, 0, 4 ), 'soft' )
            . $p::panel(
                $p::heading( esc_html__( 'Still have questions?', 'faq-for-woocommerce' ), 'center', '24px' )
                . $p::lead( esc_html__( 'We’re here to help. Send us a message and we’ll get back to you quickly.', 'faq-for-woocommerce' ), 'center', '24px' )
                . $p::button( esc_html__( 'Get in touch', 'faq-for-woocommerce' ), 'center' ),
                '#f6f6f9'
            )
        ),
    ),

    'minimal'           => array(
        'title'       => __( 'Minimal FAQ', 'faq-for-woocommerce' ),
        'description' => __( 'Hairline separators and generous spacing. Lets the words breathe.', 'faq-for-woocommerce' ),
        'group'       => 'general',
        'keywords'    => array( 'faq', 'minimal', 'simple', 'clean' ),
        'content'     => $p::section(
            $p::heading( esc_html__( 'FAQ', 'faq-for-woocommerce' ), 'left', 'clamp(28px, 3.4vw, 38px)' )
            . $p::lead( esc_html__( 'A few things worth knowing before you start.', 'faq-for-woocommerce' ), 'left', '32px' )
            . $p::custom_faqs( $general_faqs, 'minimal' ),
            '#ffffff',
            '720px'
        ),
    ),

    'two-lists'         => array(
        'title'       => __( 'Two FAQ lists side by side', 'faq-for-woocommerce' ),
        'description' => __( 'Split questions into two topics, such as General and Account.', 'faq-for-woocommerce' ),
        'group'       => 'general',
        'keywords'    => array( 'faq', 'columns', 'topics' ),
        'content'     => $p::section(
            $p::eyebrow( esc_html__( 'FAQ', 'faq-for-woocommerce' ), '#4f46e5' )
            . $p::heading( esc_html__( 'How can we help?', 'faq-for-woocommerce' ) )
            . $p::lead( esc_html__( 'Pick a topic to find your answer.', 'faq-for-woocommerce' ) )
            . $p::columns(
                $p::heading( esc_html__( 'General', 'faq-for-woocommerce' ), 'left', '20px' )
                . $p::custom_faqs( array_slice( $general_faqs, 0, 3 ), 'soft' ),
                $p::heading( esc_html__( 'Your account', 'faq-for-woocommerce' ), 'left', '20px' )
                . $p::custom_faqs(
                    array(
                        array( __( 'How do I reset my password?', 'faq-for-woocommerce' ), __( 'Choose “Forgot password” on the login screen and follow the link we email you. The link works for one hour.', 'faq-for-woocommerce' ) ),
                        array( __( 'Can I change my email address?', 'faq-for-woocommerce' ), __( 'Yes. Open your account settings, enter the new address and confirm it from your inbox.', 'faq-for-woocommerce' ) ),
                        array( __( 'How do I delete my account?', 'faq-for-woocommerce' ), __( 'Contact us from the email linked to your account and we will remove it and your data within 30 days.', 'faq-for-woocommerce' ) ),
                    ),
                    'soft'
                ),
                '50%'
            ),
            '#ffffff',
            '1120px'
        ),
    ),

    'help-center'       => array(
        'title'       => __( 'Help center', 'faq-for-woocommerce' ),
        'description' => __( 'A help-center hero with search and category filters over your FAQ library.', 'faq-for-woocommerce' ),
        'group'       => 'general',
        'keywords'    => array( 'help center', 'support', 'search', 'knowledge base' ),
        'content'     => $p::section(
            $p::eyebrow( esc_html__( 'Help center', 'faq-for-woocommerce' ), '#4f46e5' )
            . $p::heading( esc_html__( 'How can we help you today?', 'faq-for-woocommerce' ), 'center', 'clamp(32px, 4.6vw, 48px)' )
            . $p::lead( esc_html__( 'Search our answers or browse by topic.', 'faq-for-woocommerce' ) )
            . $p::prebuilt(
                array(
                    'source'     => 'all',
                    'showSearch' => true,
                    'showFilter' => true,
                    'perPage'    => 8,
                ),
                'cards'
            ),
            '#f7f7fb',
            '820px'
        ),
    ),

    /* ---------------------------------------------------------- Live lists */

    'most-viewed'       => array(
        'title'       => __( 'Most viewed questions', 'faq-for-woocommerce' ),
        'description' => __( 'Your FAQs that visitors open most, updated automatically.', 'faq-for-woocommerce' ),
        'group'       => 'dynamic',
        'keywords'    => array( 'popular', 'top', 'most viewed', 'trending' ),
        'content'     => $p::section(
            $p::eyebrow( esc_html__( 'Popular', 'faq-for-woocommerce' ), '#b45309' )
            . $p::heading( esc_html__( 'Most asked questions', 'faq-for-woocommerce' ) )
            . $p::lead( esc_html__( 'The answers people look for most, updated as they browse.', 'faq-for-woocommerce' ) )
            . $p::prebuilt(
                array(
                    'source' => 'popular',
                    'limit'  => 5,
                ),
                'cards'
            )
        ),
    ),

    'newest'            => array(
        'title'       => __( 'Newest questions', 'faq-for-woocommerce' ),
        'description' => __( 'Your latest FAQs, so returning visitors see what’s new.', 'faq-for-woocommerce' ),
        'group'       => 'dynamic',
        'keywords'    => array( 'recent', 'new', 'latest', 'updates' ),
        'content'     => $p::section(
            $p::eyebrow( esc_html__( 'Just added', 'faq-for-woocommerce' ), '#0369a1' )
            . $p::heading( esc_html__( 'New answers', 'faq-for-woocommerce' ) )
            . $p::lead( esc_html__( 'The latest questions we’ve answered.', 'faq-for-woocommerce' ) )
            . $p::prebuilt(
                array(
                    'source' => 'recent',
                    'limit'  => 5,
                ),
                'minimal'
            ),
            '#ffffff'
        ),
    ),

    /* ---------------------------------------------------------------- Store */

    'shipping-returns'  => array(
        'title'       => __( 'Shipping & returns', 'faq-for-woocommerce' ),
        'description' => __( 'Delivery times, costs, tracking and returns for an online store.', 'faq-for-woocommerce' ),
        'group'       => 'store',
        'keywords'    => array( 'shipping', 'delivery', 'returns', 'woocommerce', 'store' ),
        'content'     => $p::section(
            $p::eyebrow( esc_html__( 'Shipping & returns', 'faq-for-woocommerce' ), '#047857' )
            . $p::heading( esc_html__( 'Delivery made simple', 'faq-for-woocommerce' ) )
            . $p::lead( esc_html__( 'Everything about getting your order, and sending it back if it’s not right.', 'faq-for-woocommerce' ) )
            . $p::custom_faqs(
                array(
                    array( __( 'How long does delivery take?', 'faq-for-woocommerce' ), __( 'Most orders arrive in 2–5 business days. You’ll get a tracking link by email as soon as your order ships.', 'faq-for-woocommerce' ) ),
                    array( __( 'How much does shipping cost?', 'faq-for-woocommerce' ), __( 'Shipping is calculated at checkout based on your address. Orders over a set amount ship free.', 'faq-for-woocommerce' ) ),
                    array( __( 'Do you ship internationally?', 'faq-for-woocommerce' ), __( 'Yes, we ship to many countries. Import duties and taxes may apply and are paid on delivery.', 'faq-for-woocommerce' ) ),
                    array( __( 'How do I return an item?', 'faq-for-woocommerce' ), __( 'Start a return from your account within 30 days of delivery. Items must be unused and in their original packaging.', 'faq-for-woocommerce' ) ),
                    array( __( 'When will I get my refund?', 'faq-for-woocommerce' ), __( 'Refunds go back to your original payment method within 5–10 business days after we receive your return.', 'faq-for-woocommerce' ) ),
                ),
                'cards'
            ),
            '#f7f7fb'
        ),
    ),

    'orders-payments'   => array(
        'title'       => __( 'Orders & payments', 'faq-for-woocommerce' ),
        'description' => __( 'Payment methods, order changes, invoices and security.', 'faq-for-woocommerce' ),
        'group'       => 'store',
        'keywords'    => array( 'payments', 'orders', 'checkout', 'invoice', 'store' ),
        'content'     => $p::section(
            $p::columns(
                $p::eyebrow( esc_html__( 'Orders', 'faq-for-woocommerce' ), '#6d28d9', 'left' )
                . $p::heading( esc_html__( 'Ordering & payment', 'faq-for-woocommerce' ), 'left', 'clamp(28px, 3.4vw, 38px)' )
                . $p::lead( esc_html__( 'Secure checkout and flexible ways to pay.', 'faq-for-woocommerce' ), 'left', '28px' )
                . $p::button( esc_html__( 'Track my order', 'faq-for-woocommerce' ), 'left', '#track' ),
                $p::custom_faqs(
                    array(
                        array( __( 'Which payment methods do you accept?', 'faq-for-woocommerce' ), __( 'We accept major credit and debit cards and the other options shown at checkout.', 'faq-for-woocommerce' ) ),
                        array( __( 'Is my payment secure?', 'faq-for-woocommerce' ), __( 'Yes. Payments are encrypted and processed by our payment provider. We never store your full card details.', 'faq-for-woocommerce' ) ),
                        array( __( 'Can I change or cancel my order?', 'faq-for-woocommerce' ), __( 'Contact us as soon as possible. We can change or cancel orders that haven’t shipped yet.', 'faq-for-woocommerce' ) ),
                        array( __( 'Where can I find my invoice?', 'faq-for-woocommerce' ), __( 'Your invoice is attached to the order confirmation email and available in your account.', 'faq-for-woocommerce' ) ),
                    ),
                    'soft'
                )
            ),
            '#ffffff',
            '1120px'
        ),
    ),

    'product-faqs'      => array(
        'title'       => __( 'Product questions', 'faq-for-woocommerce' ),
        'description' => __( 'For product templates: shows the FAQs assigned to the current product.', 'faq-for-woocommerce' ),
        'group'       => 'store',
        'keywords'    => array( 'product', 'woocommerce', 'template', 'single product' ),
        'content'     => $p::section(
            $p::heading( esc_html__( 'Questions about this product', 'faq-for-woocommerce' ), 'left', 'clamp(24px, 3vw, 30px)' )
            . $p::prebuilt( array( 'source' => 'current_product' ), 'cards' ),
            '#ffffff',
            '820px'
        ),
        // Gallery preview has no product, so show library FAQs instead.
        'preview'     => $p::section(
            $p::heading( esc_html__( 'Questions about this product', 'faq-for-woocommerce' ), 'left', 'clamp(24px, 3vw, 30px)' )
            . $p::prebuilt(
                array(
                    'source' => 'all',
                    'limit'  => 4,
                ),
                'cards'
            ),
            '#ffffff',
            '820px'
        ),
    ),

    /* ------------------------------------------------------------ Industries */

    'saas-pricing'      => array(
        'title'       => __( 'Pricing & billing', 'faq-for-woocommerce' ),
        'description' => __( 'For SaaS and membership pricing pages: trials, plans, billing and cancellation.', 'faq-for-woocommerce' ),
        'group'       => 'industry',
        'keywords'    => array( 'saas', 'pricing', 'billing', 'subscription', 'plans' ),
        'content'     => $p::section(
            $p::eyebrow( esc_html__( 'Pricing', 'faq-for-woocommerce' ), '#4f46e5' )
            . $p::heading( esc_html__( 'Pricing questions', 'faq-for-woocommerce' ) )
            . $p::lead( esc_html__( 'Simple plans, no surprises.', 'faq-for-woocommerce' ) )
            . $p::custom_faqs(
                array(
                    array( __( 'Is there a free trial?', 'faq-for-woocommerce' ), __( 'Yes. Try every feature free for 14 days. No credit card is needed to start.', 'faq-for-woocommerce' ) ),
                    array( __( 'Can I switch plans later?', 'faq-for-woocommerce' ), __( 'Any time. Upgrades apply immediately and downgrades take effect at your next billing date.', 'faq-for-woocommerce' ) ),
                    array( __( 'Do you offer annual billing?', 'faq-for-woocommerce' ), __( 'Yes. Pay yearly and get two months free compared with monthly billing.', 'faq-for-woocommerce' ) ),
                    array( __( 'How do I cancel?', 'faq-for-woocommerce' ), __( 'Cancel from your account settings in two clicks. You keep access until the end of your billing period.', 'faq-for-woocommerce' ) ),
                    array( __( 'Do you offer discounts for non-profits?', 'faq-for-woocommerce' ), __( 'Yes. Contact us with details about your organization and we’ll set you up.', 'faq-for-woocommerce' ) ),
                ),
                'cards'
            ),
            '#f7f7fb'
        ),
    ),

    'software-onboarding' => array(
        'title'       => __( 'Getting started', 'faq-for-woocommerce' ),
        'description' => __( 'For apps and software: setup, integrations, data and team access.', 'faq-for-woocommerce' ),
        'group'       => 'industry',
        'keywords'    => array( 'software', 'app', 'onboarding', 'setup', 'saas' ),
        'content'     => $p::section(
            $p::eyebrow( esc_html__( 'Getting started', 'faq-for-woocommerce' ), '#0369a1' )
            . $p::heading( esc_html__( 'Up and running in minutes', 'faq-for-woocommerce' ) )
            . $p::lead( esc_html__( 'Answers to what new users ask first.', 'faq-for-woocommerce' ) )
            . $p::custom_faqs(
                array(
                    array( __( 'How long does setup take?', 'faq-for-woocommerce' ), __( 'Most teams are set up in under ten minutes. Our guided setup walks you through each step.', 'faq-for-woocommerce' ) ),
                    array( __( 'Can I import my existing data?', 'faq-for-woocommerce' ), __( 'Yes. Import from a CSV file or connect the tools you already use.', 'faq-for-woocommerce' ) ),
                    array( __( 'Can I invite my team?', 'faq-for-woocommerce' ), __( 'Invite teammates from settings and choose what each person can see and edit.', 'faq-for-woocommerce' ) ),
                    array( __( 'Is my data backed up?', 'faq-for-woocommerce' ), __( 'Your data is backed up automatically every day and stored securely.', 'faq-for-woocommerce' ) ),
                ),
                'minimal'
            )
        ),
    ),

    'agency-services'   => array(
        'title'       => __( 'Services & process', 'faq-for-woocommerce' ),
        'description' => __( 'For agencies, studios and freelancers: process, timelines and pricing.', 'faq-for-woocommerce' ),
        'group'       => 'industry',
        'keywords'    => array( 'agency', 'services', 'freelancer', 'studio', 'consulting' ),
        'content'     => $p::section(
            $p::columns(
                $p::eyebrow( esc_html__( 'Working together', 'faq-for-woocommerce' ), '#be123c', 'left' )
                . $p::heading( esc_html__( 'How we work', 'faq-for-woocommerce' ), 'left', 'clamp(28px, 3.4vw, 38px)' )
                . $p::lead( esc_html__( 'A clear process from first call to launch.', 'faq-for-woocommerce' ), 'left', '28px' )
                . $p::button( esc_html__( 'Book a call', 'faq-for-woocommerce' ), 'left', '#book' ),
                $p::custom_faqs(
                    array(
                        array( __( 'What does a typical project look like?', 'faq-for-woocommerce' ), __( 'We start with a short discovery call, share a proposal, then work in clear milestones with regular check-ins.', 'faq-for-woocommerce' ) ),
                        array( __( 'How long does a project take?', 'faq-for-woocommerce' ), __( 'Most projects take four to eight weeks, depending on scope. We agree on the timeline before we start.', 'faq-for-woocommerce' ) ),
                        array( __( 'How do you price your work?', 'faq-for-woocommerce' ), __( 'We quote a fixed price per project, so you know the full cost up front. No hidden fees.', 'faq-for-woocommerce' ) ),
                        array( __( 'Do you offer support after launch?', 'faq-for-woocommerce' ), __( 'Yes. Every project includes 30 days of support, and ongoing care plans are available.', 'faq-for-woocommerce' ) ),
                    ),
                    'cards'
                )
            ),
            '#ffffff',
            '1120px'
        ),
    ),

    'online-course'     => array(
        'title'       => __( 'Course FAQ', 'faq-for-woocommerce' ),
        'description' => __( 'For online courses and coaching: access, pace, certificates and refunds.', 'faq-for-woocommerce' ),
        'group'       => 'industry',
        'keywords'    => array( 'course', 'education', 'learning', 'lms', 'coaching' ),
        'content'     => $p::section(
            $p::eyebrow( esc_html__( 'Course', 'faq-for-woocommerce' ), '#047857' )
            . $p::heading( esc_html__( 'Before you enroll', 'faq-for-woocommerce' ) )
            . $p::lead( esc_html__( 'What to expect from the course.', 'faq-for-woocommerce' ) )
            . $p::custom_faqs(
                array(
                    array( __( 'How long do I have access?', 'faq-for-woocommerce' ), __( 'You get lifetime access, including all future updates to the course.', 'faq-for-woocommerce' ) ),
                    array( __( 'Can I learn at my own pace?', 'faq-for-woocommerce' ), __( 'Yes. Lessons are on demand, so you can start, pause and continue whenever it suits you.', 'faq-for-woocommerce' ) ),
                    array( __( 'Do I get a certificate?', 'faq-for-woocommerce' ), __( 'You receive a certificate of completion when you finish all lessons.', 'faq-for-woocommerce' ) ),
                    array( __( 'What if the course isn’t for me?', 'faq-for-woocommerce' ), __( 'Ask for a full refund within 14 days of purchase. No questions asked.', 'faq-for-woocommerce' ) ),
                ),
                'soft'
            )
        ),
    ),

    'restaurant'        => array(
        'title'       => __( 'Restaurant & reservations', 'faq-for-woocommerce' ),
        'description' => __( 'For restaurants and cafés: bookings, dietary needs, groups and parking.', 'faq-for-woocommerce' ),
        'group'       => 'industry',
        'keywords'    => array( 'restaurant', 'cafe', 'food', 'reservation', 'booking' ),
        'content'     => $p::section(
            $p::eyebrow( esc_html__( 'Visit us', 'faq-for-woocommerce' ), '#b45309' )
            . $p::heading( esc_html__( 'Good to know before you visit', 'faq-for-woocommerce' ) )
            . $p::lead( esc_html__( 'Bookings, menus and everything in between.', 'faq-for-woocommerce' ) )
            . $p::custom_faqs(
                array(
                    array( __( 'Do I need a reservation?', 'faq-for-woocommerce' ), __( 'Walk-ins are welcome, but we recommend booking for evenings and weekends.', 'faq-for-woocommerce' ) ),
                    array( __( 'Can you cater for dietary needs?', 'faq-for-woocommerce' ), __( 'Yes. We have vegetarian, vegan and gluten-free options. Let us know about allergies when you book.', 'faq-for-woocommerce' ) ),
                    array( __( 'Do you take group bookings?', 'faq-for-woocommerce' ), __( 'We welcome groups of up to 20. For larger parties, contact us to arrange a private space.', 'faq-for-woocommerce' ) ),
                    array( __( 'Is there parking nearby?', 'faq-for-woocommerce' ), __( 'Street parking and a public car park are a short walk away.', 'faq-for-woocommerce' ) ),
                ),
                'minimal'
            ),
            '#fbfaf8'
        ),
    ),

    'events'            => array(
        'title'       => __( 'Event FAQ', 'faq-for-woocommerce' ),
        'description' => __( 'For conferences, workshops and meetups: tickets, venue and schedule.', 'faq-for-woocommerce' ),
        'group'       => 'industry',
        'keywords'    => array( 'event', 'conference', 'tickets', 'workshop', 'meetup' ),
        'content'     => $p::section(
            $p::eyebrow( esc_html__( 'Event info', 'faq-for-woocommerce' ), '#6d28d9' )
            . $p::heading( esc_html__( 'Everything about the event', 'faq-for-woocommerce' ) )
            . $p::lead( esc_html__( 'Tickets, venue and what to bring.', 'faq-for-woocommerce' ) )
            . $p::custom_faqs(
                array(
                    array( __( 'Where is the event held?', 'faq-for-woocommerce' ), __( 'At our main venue in the city center. Full address and directions are in your ticket email.', 'faq-for-woocommerce' ) ),
                    array( __( 'Can I transfer my ticket?', 'faq-for-woocommerce' ), __( 'Yes. Update the attendee name from your ticket link up to 48 hours before the event.', 'faq-for-woocommerce' ) ),
                    array( __( 'Will sessions be recorded?', 'faq-for-woocommerce' ), __( 'Main sessions are recorded and shared with all ticket holders within a week.', 'faq-for-woocommerce' ) ),
                    array( __( 'Is the venue accessible?', 'faq-for-woocommerce' ), __( 'Yes. The venue is step-free throughout. Contact us about any other needs.', 'faq-for-woocommerce' ) ),
                ),
                'cards'
            ),
            '#f7f7fb'
        ),
    ),

    'hotel'             => array(
        'title'       => __( 'Hotel & bookings', 'faq-for-woocommerce' ),
        'description' => __( 'For hotels, rentals and travel: check-in, cancellations and amenities.', 'faq-for-woocommerce' ),
        'group'       => 'industry',
        'keywords'    => array( 'hotel', 'travel', 'booking', 'rental', 'hospitality' ),
        'content'     => $p::section(
            $p::eyebrow( esc_html__( 'Your stay', 'faq-for-woocommerce' ), '#0f766e' )
            . $p::heading( esc_html__( 'Plan your stay', 'faq-for-woocommerce' ) )
            . $p::lead( esc_html__( 'Check-in, cancellations and what’s included.', 'faq-for-woocommerce' ) )
            . $p::custom_faqs(
                array(
                    array( __( 'What are check-in and check-out times?', 'faq-for-woocommerce' ), __( 'Check-in is from 3pm and check-out is until 11am. Early check-in is possible on request.', 'faq-for-woocommerce' ) ),
                    array( __( 'Can I cancel my booking?', 'faq-for-woocommerce' ), __( 'Free cancellation is available up to 48 hours before arrival on flexible rates.', 'faq-for-woocommerce' ) ),
                    array( __( 'Is breakfast included?', 'faq-for-woocommerce' ), __( 'Breakfast is included with most rates. Your confirmation email shows what’s included.', 'faq-for-woocommerce' ) ),
                    array( __( 'Do you allow pets?', 'faq-for-woocommerce' ), __( 'Well-behaved pets are welcome in selected rooms. Please let us know when you book.', 'faq-for-woocommerce' ) ),
                ),
                'soft'
            )
        ),
    ),

    'fitness'           => array(
        'title'       => __( 'Membership FAQ', 'faq-for-woocommerce' ),
        'description' => __( 'For gyms, studios and clubs: memberships, classes and freezing.', 'faq-for-woocommerce' ),
        'group'       => 'industry',
        'keywords'    => array( 'gym', 'fitness', 'membership', 'studio', 'classes' ),
        'content'     => $p::section(
            $p::columns(
                $p::eyebrow( esc_html__( 'Membership', 'faq-for-woocommerce' ), '#be123c', 'left' )
                . $p::heading( esc_html__( 'Join with confidence', 'faq-for-woocommerce' ), 'left', 'clamp(28px, 3.4vw, 38px)' )
                . $p::lead( esc_html__( 'Flexible memberships that fit your routine.', 'faq-for-woocommerce' ), 'left', '28px' )
                . $p::button( esc_html__( 'View memberships', 'faq-for-woocommerce' ), 'left', '#memberships' ),
                $p::custom_faqs(
                    array(
                        array( __( 'Is there a joining fee?', 'faq-for-woocommerce' ), __( 'No joining fee. Pay monthly with no long-term contract.', 'faq-for-woocommerce' ) ),
                        array( __( 'Can I try a class first?', 'faq-for-woocommerce' ), __( 'Yes. Your first class is free. Book it online or at the front desk.', 'faq-for-woocommerce' ) ),
                        array( __( 'Can I pause my membership?', 'faq-for-woocommerce' ), __( 'Freeze your membership for up to three months a year, for travel or injury.', 'faq-for-woocommerce' ) ),
                        array( __( 'What are your opening hours?', 'faq-for-woocommerce' ), __( 'We’re open early until late on weekdays and from morning to evening at weekends.', 'faq-for-woocommerce' ) ),
                    ),
                    'minimal'
                )
            ),
            '#ffffff',
            '1120px'
        ),
    ),

    'real-estate'       => array(
        'title'       => __( 'Buying & renting', 'faq-for-woocommerce' ),
        'description' => __( 'For real estate agents and property managers: viewings, offers and renting.', 'faq-for-woocommerce' ),
        'group'       => 'industry',
        'keywords'    => array( 'real estate', 'property', 'rent', 'buy', 'agent' ),
        'content'     => $p::section(
            $p::eyebrow( esc_html__( 'Property', 'faq-for-woocommerce' ), '#047857' )
            . $p::heading( esc_html__( 'Buying or renting with us', 'faq-for-woocommerce' ) )
            . $p::lead( esc_html__( 'Clear answers for every step of your move.', 'faq-for-woocommerce' ) )
            . $p::custom_faqs(
                array(
                    array( __( 'How do I book a viewing?', 'faq-for-woocommerce' ), __( 'Request a viewing from any listing or call us. We’ll confirm a time that suits you.', 'faq-for-woocommerce' ) ),
                    array( __( 'What do I need to rent a property?', 'faq-for-woocommerce' ), __( 'Usually proof of identity, proof of income and a reference. We’ll tell you exactly what’s needed.', 'faq-for-woocommerce' ) ),
                    array( __( 'How do I make an offer?', 'faq-for-woocommerce' ), __( 'Tell us your offer and any conditions. We pass it to the seller and keep you updated.', 'faq-for-woocommerce' ) ),
                    array( __( 'Are there any fees?', 'faq-for-woocommerce' ), __( 'Any fees are listed clearly before you commit. There are no hidden charges.', 'faq-for-woocommerce' ) ),
                ),
                'cards'
            ),
            '#f7f7fb'
        ),
    ),

    'privacy-security'  => array(
        'title'       => __( 'Privacy & security', 'faq-for-woocommerce' ),
        'description' => __( 'For any business: how data is collected, stored, protected and deleted.', 'faq-for-woocommerce' ),
        'group'       => 'industry',
        'keywords'    => array( 'privacy', 'security', 'data', 'gdpr', 'trust' ),
        'content'     => $p::section(
            $p::eyebrow( esc_html__( 'Trust', 'faq-for-woocommerce' ), '#0369a1' )
            . $p::heading( esc_html__( 'Privacy & security', 'faq-for-woocommerce' ) )
            . $p::lead( esc_html__( 'How we look after your information.', 'faq-for-woocommerce' ) )
            . $p::custom_faqs(
                array(
                    array( __( 'What data do you collect?', 'faq-for-woocommerce' ), __( 'Only what we need to provide our service, such as your name, email and order details.', 'faq-for-woocommerce' ) ),
                    array( __( 'Do you share my data?', 'faq-for-woocommerce' ), __( 'We never sell your data. We share it only with trusted partners who help us run our service.', 'faq-for-woocommerce' ) ),
                    array( __( 'How is my data protected?', 'faq-for-woocommerce' ), __( 'Your data is encrypted in transit and stored securely, with access limited to people who need it.', 'faq-for-woocommerce' ) ),
                    array( __( 'Can I ask you to delete my data?', 'faq-for-woocommerce' ), __( 'Yes. Contact us and we’ll delete your personal data, unless we must keep it by law.', 'faq-for-woocommerce' ) ),
                ),
                'minimal'
            )
        ),
    ),
);
