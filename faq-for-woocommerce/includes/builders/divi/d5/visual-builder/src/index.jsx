import React, { useEffect, useState } from 'react';

import metadata from './module.json';
import conversionOutline from './conversion-outline.json';
import customFaqsMetadata from './custom-faqs/module.json';
import customFaqsConversionOutline from './custom-faqs/conversion-outline.json';
import customFaqItemMetadata from './custom-faq-item/module.json';
import customFaqItemConversionOutline from './custom-faq-item/conversion-outline.json';

const { addAction, addFilter } = window?.vendor?.wp?.hooks || window?.wp?.hooks || {};
const { __ } = window?.wp?.i18n || { __: (text) => text };
const apiFetch = window?.wp?.apiFetch;
const {
    ModuleContainer,
    ChildModulesContainer,
    StyleContainer,
    elementClassnames,
} = window?.divi?.module || {};
const { registerModule } = window?.divi?.moduleLibrary || {};
const { getAttrByMode } = window?.divi?.moduleUtils || {};

function readInner(attrs, name, fallback) {
  if (typeof getAttrByMode === 'function') {
    const value = getAttrByMode(attrs?.[name]?.innerContent);
    if (value !== undefined && value !== null && value !== '') {
      return value;
    }
  }

  const desktop = attrs?.[name]?.innerContent?.desktop?.value;
  return desktop === undefined || desktop === null || desktop === '' ? fallback : desktop;
}

function readInnerText(attrs, name, fallback) {
  const value = readInner(attrs, name, fallback);
  if (value && typeof value === 'object') {
    if (typeof value.html === 'string') {
      return value.html;
    }
    if (typeof value.text === 'string') {
      return value.text;
    }
    if (typeof value.raw === 'string') {
      return value.raw;
    }
  }
  return value;
}

const ModuleStyles = ({
  attrs,
  elements,
  settings,
  orderClass,
  mode,
  state,
  noStyleTag,
}) => (
  <StyleContainer mode={mode} state={state} noStyleTag={noStyleTag}>
    {elements.style({
      attrName: 'module',
      styleProps: {
        disabledOn: {
          disabledModuleVisibility: settings?.disabledModuleVisibility,
        },
      },
    })}
    {elements.style({ attrName: 'faqQuestion' })}
    {elements.style({ attrName: 'faqAnswer' })}
  </StyleContainer>
);

const CustomFaqsStyles = ({
  attrs,
  elements,
  settings,
  orderClass,
  mode,
  state,
  noStyleTag,
}) => (
  <StyleContainer mode={mode} state={state} noStyleTag={noStyleTag}>
    {elements.style({
      attrName: 'module',
      styleProps: {
        disabledOn: {
          disabledModuleVisibility: settings?.disabledModuleVisibility,
        },
      },
    })}
    {elements.style({ attrName: 'faqQuestion' })}
    {elements.style({ attrName: 'faqAnswer' })}
    {elements.style({ attrName: 'faqItem' })}
    {elements.style({ attrName: 'faqIcon' })}
    {elements.style({ attrName: 'faqSearch' })}
  </StyleContainer>
);

const CustomFaqItemStyles = ({
  elements,
  settings,
  mode,
  state,
  noStyleTag,
}) => (
  <StyleContainer mode={mode} state={state} noStyleTag={noStyleTag}>
    {elements.style({
      attrName: 'module',
      styleProps: {
        disabledOn: {
          disabledModuleVisibility: settings?.disabledModuleVisibility,
        },
      },
    })}
  </StyleContainer>
);

const ModuleScriptData = ({ elements }) => (
  <React.Fragment>
    {elements.scriptData({
      attrName: 'module',
    })}
  </React.Fragment>
);

const moduleClassnames = ({ classnamesInstance, attrs }) => {
  classnamesInstance.add(
    elementClassnames({
      attrs: attrs?.module?.decoration ?? {},
    })
  );
};

const DisplayFaqsEdit = ({ attrs, id, name, elements }) => {
  const [html, setHtml] = useState('');
  const [loading, setLoading] = useState(true);

  const source = readInner(attrs, 'faqSource', 'all');
  const faqCat = readInner(attrs, 'faqCat', '');
  const faqIds = readInner(attrs, 'faqIds', '');
  const groupId = readInner(attrs, 'faqGroup', '');
  const productId = readInner(attrs, 'productId', '');
  const layout = readInner(attrs, 'layout', '0');
  const displayMode = readInner(attrs, 'displayMode', 'accordion');
  const faqLimit = readInner(attrs, 'faqLimit', '0');
  const orderBy = readInner(attrs, 'orderBy', 'id');
  const faqOrder = readInner(attrs, 'faqOrder', 'ASC');
  const showSearch = readInner(attrs, 'showSearch', 'on');
  const enableSchema = readInner(attrs, 'enableSchema', 'on');
  const expandFirst = readInner(attrs, 'expandFirst', 'off');

  useEffect(() => {
    if (typeof apiFetch !== 'function') {
      setLoading(false);
      return;
    }

    const params = new URLSearchParams({
      faq_source: String(source),
      faq_cat: String(faqCat),
      faq_ids: String(faqIds),
      group_id: String(groupId),
      product_id: String(productId),
      layout: String(layout),
      display_mode: String(displayMode),
      faq_limit: String(faqLimit),
      order_by: String(orderBy),
      faq_order: String(faqOrder),
      show_search: String(showSearch),
      enable_schema: String(enableSchema),
      expand_first: String(expandFirst),
    });

    setLoading(true);
    apiFetch({ path: `/happy-faq/v1/render?${params.toString()}` })
      .then((response) => {
        setHtml(response && response.html ? response.html : '');
        setLoading(false);
      })
      .catch(() => {
        setHtml('');
        setLoading(false);
      });
  }, [
    source,
    faqCat,
    faqIds,
    groupId,
    productId,
    layout,
    displayMode,
    faqLimit,
    orderBy,
    faqOrder,
    showSearch,
    enableSchema,
    expandFirst,
  ]);

  return (
    <ModuleContainer
      attrs={attrs}
      elements={elements}
      id={id}
      moduleClassName="ffw_display_faqs"
      name={name}
      scriptDataComponent={ModuleScriptData}
      stylesComponent={ModuleStyles}
      classnamesFunction={moduleClassnames}
    >
      {elements.styleComponents({
        attrName: 'module',
      })}
      <div className="et_pb_module_inner">
        {loading && (
          <p className="ffw-builder-placeholder">
            {__('Loading FAQs…', 'faq-for-woocommerce')}
          </p>
        )}
        {!loading && html && (
          <div
            className="ffw-divi5-preview"
            dangerouslySetInnerHTML={{ __html: html }}
          />
        )}
        {!loading && !html && (
          <p className="ffw-builder-placeholder">
            {__('No FAQs found for this source. Create FAQs or choose another source.', 'faq-for-woocommerce')}
          </p>
        )}
      </div>
    </ModuleContainer>
  );
};

const CustomFaqsEdit = ({ attrs, id, name, elements, childrenIds }) => (
  <ModuleContainer
    attrs={attrs}
    elements={elements}
    id={id}
    moduleClassName="ffw_custom_faqs"
    name={name}
    childrenIds={childrenIds}
    scriptDataComponent={ModuleScriptData}
    stylesComponent={CustomFaqsStyles}
    classnamesFunction={moduleClassnames}
  >
    {elements.styleComponents({
      attrName: 'module',
    })}
    {typeof ChildModulesContainer === 'function' ? (
      <ChildModulesContainer ids={childrenIds} />
    ) : (
      <div className="et_pb_module_inner">
        <p className="ffw-builder-placeholder">
          {__('Add FAQ Items in the layers panel. They stay on this page.', 'faq-for-woocommerce')}
        </p>
      </div>
    )}
  </ModuleContainer>
);

const CustomFaqItemEdit = ({ attrs, id, name, elements }) => {
  const question = readInnerText(attrs, 'question', '');
  const answer = readInnerText(attrs, 'answer', '');
  const canRender = typeof elements?.render === 'function';

  return (
    <ModuleContainer
      attrs={attrs}
      elements={elements}
      id={id}
      moduleClassName="ffw_custom_faq_item"
      name={name}
      stylesComponent={CustomFaqItemStyles}
      classnamesFunction={moduleClassnames}
    >
      {canRender ? (
        <React.Fragment>
          {elements.render({ attrName: 'question' })}
          {elements.render({ attrName: 'answer' })}
        </React.Fragment>
      ) : (
        <div className="et_pb_module_inner">
          <div className="ffw-divi-custom-faq-item__question">
            {question || __('Question', 'faq-for-woocommerce')}
          </div>
          {answer ? (
            <div
              className="ffw-divi-custom-faq-item__answer"
              dangerouslySetInnerHTML={{ __html: answer }}
            />
          ) : (
            <div className="ffw-divi-custom-faq-item__answer">
              {__('Answer', 'faq-for-woocommerce')}
            </div>
          )}
        </div>
      )}
    </ModuleContainer>
  );
};

const displayFaqsModule = {
  metadata,
  conversionOutline,
  renderers: {
    edit: DisplayFaqsEdit,
  },
  placeholderContent: {
    faqSource: {
      innerContent: {
        desktop: {
          value: 'all',
        },
      },
    },
    showSearch: {
      innerContent: {
        desktop: {
          value: 'on',
        },
      },
    },
    enableSchema: {
      innerContent: {
        desktop: {
          value: 'on',
        },
      },
    },
  },
};

const customFaqItemModule = {
  metadata: customFaqItemMetadata,
  conversionOutline: customFaqItemConversionOutline,
  renderers: {
    edit: CustomFaqItemEdit,
  },
  placeholderContent: {
    question: {
      innerContent: {
        desktop: {
          value: __('Your question', 'faq-for-woocommerce'),
        },
      },
    },
    answer: {
      innerContent: {
        desktop: {
          value: `<p>${__('Your answer', 'faq-for-woocommerce')}</p>`,
        },
      },
    },
  },
};

const customFaqsModule = {
  metadata: customFaqsMetadata,
  conversionOutline: customFaqsConversionOutline,
  renderers: {
    edit: CustomFaqsEdit,
  },
  placeholderContent: {
    displayMode: {
      innerContent: {
        desktop: {
          value: 'accordion',
        },
      },
    },
    showSearch: {
      innerContent: {
        desktop: {
          value: 'on',
        },
      },
    },
    enableSchema: {
      innerContent: {
        desktop: {
          value: 'on',
        },
      },
    },
  },
};

if (typeof addFilter === 'function') {
  addFilter(
    'divi.moduleLibrary.conversion.moduleConversionOutline',
    'happyFaq.diviFaqs.conversionOutline',
    (outline, moduleName) => {
      if ('happy-faq/divi-faqs' === moduleName) {
        return conversionOutline;
      }
      if ('happy-faq/divi-custom-faqs' === moduleName) {
        return customFaqsConversionOutline;
      }
      if ('happy-faq/divi-custom-faq-item' === moduleName) {
        return customFaqItemConversionOutline;
      }
      return outline;
    }
  );
}

if (typeof addAction === 'function' && typeof registerModule === 'function') {
  addAction(
    'divi.moduleLibrary.registerModuleLibraryStore.after',
    'happyFaq.diviFaqs',
    () => {
      registerModule(displayFaqsModule.metadata, displayFaqsModule);
      registerModule(customFaqItemModule.metadata, customFaqItemModule);
      registerModule(customFaqsModule.metadata, customFaqsModule);
    }
  );
}
