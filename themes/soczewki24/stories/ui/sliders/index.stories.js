import noUiSlider from 'nouislider';
import wNumb from 'wnumb';
import labelTopFloatingContent from './label-top-floating.html'
import labelBottomFloatingContent from './label-bottom-floating.html'
import labelBottomContent from './label-bottom.html'
import singleContent from './single.html'
import pipsContent from './pips.html'

export default {
  title: 'UI/Sliders',
  parameters: {
    docs: {
      description: {
        component: 'Range slider oparty na noUiSlider (juz uzywany w motywie do filtra ceny w faceted search). Style sa w src/scss/prestashop/components/nouislider/_vars.scss.'
      }
    }
  }
};

export const LabelTopFloating = () => labelTopFloatingContent;
LabelTopFloating.storyName = 'Label - top floating';
LabelTopFloating.parameters = {
  docs: {
    source: {
      code: labelTopFloatingContent
    }
  }
};
LabelTopFloating.play = async ({canvasElement}) => {
  const slider = canvasElement.querySelector('#label-top-floating-slider');

  if (slider.noUiSlider) {
    return;
  }

  const format = wNumb({decimals: 0, suffix: ' zl'});

  noUiSlider.create(slider, {
    start: [20, 80],
    connect: true,
    range: {min: 0, max: 100},
    tooltips: [format, format],
    pips: false,
  });
};

export const LabelBottomFloating = () => labelBottomFloatingContent;
LabelBottomFloating.storyName = 'Label - bottom floating';
LabelBottomFloating.parameters = {
  docs: {
    source: {
      code: labelBottomFloatingContent
    }
  }
};
LabelBottomFloating.play = async ({canvasElement}) => {
  const slider = canvasElement.querySelector('#label-bottom-floating-slider');

  if (slider.noUiSlider) {
    return;
  }

  const format = wNumb({decimals: 0, suffix: ' zl'});

  noUiSlider.create(slider, {
    start: [20, 80],
    connect: true,
    range: {min: 0, max: 100},
    tooltips: [format, format],
    pips: false,
  });
};

export const LabelBottom = () => labelBottomContent;
LabelBottom.storyName = 'Label - bottom plain';
LabelBottom.parameters = {
  docs: {
    source: {
      code: labelBottomContent
    }
  }
};
LabelBottom.play = async ({canvasElement}) => {
  const slider = canvasElement.querySelector('#label-bottom-slider');

  if (slider.noUiSlider) {
    return;
  }

  const format = wNumb({decimals: 0, suffix: ' zl'});

  noUiSlider.create(slider, {
    start: [20, 80],
    connect: true,
    range: {min: 0, max: 100},
    tooltips: [format, format],
    pips: false,
  });
};

export const SingleHandle = () => singleContent;
SingleHandle.storyName = 'Single handle';
SingleHandle.parameters = {
  docs: {
    source: {
      code: singleContent
    }
  }
};
SingleHandle.play = async ({canvasElement}) => {
  const slider = canvasElement.querySelector('#single-slider');
  const valuesEl = canvasElement.querySelector('#single-slider-values');

  if (slider.noUiSlider) {
    return;
  }

  const format = wNumb({decimals: 1, suffix: ' kg'});

  noUiSlider.create(slider, {
    start: [50],
    connect: [true, false],
    range: {min: 0, max: 100},
    tooltips: [format],
  });

  slider.noUiSlider.on('update', (values) => {
    valuesEl.textContent = values.join(' - ');
  });
};

export const WithPips = () => pipsContent;
WithPips.storyName = 'With pips';
WithPips.parameters = {
  docs: {
    source: {
      code: pipsContent
    }
  }
};
WithPips.play = async ({canvasElement}) => {
  const slider = canvasElement.querySelector('#pips-slider');

  if (slider.noUiSlider) {
    return;
  }

  const format = wNumb({decimals: 0, suffix: ' zl'});

  noUiSlider.create(slider, {
    start: [20, 80],
    connect: true,
    range: {min: 0, max: 100},
    tooltips: [format, format],
    pips: {
      mode: 'count',
      values: 6,
      density: 4,
      format,
    },
  });
};
