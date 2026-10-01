import backgroundContent from './background.html';
import textContent from './text.html';
import foregroundContent from './foreground.html';
import borderContent from './border.html';

export default {
  title: 'UI/Design Tokens',
  parameters: {
    docs: {
      description: {
        component: 'Podglad tokenow z src/scss/tokens/.'
      }
    }
  }
};

export const Background = () => backgroundContent;
Background.storyName = 'Background';
Background.parameters = {
  docs: {
    description: {
      story: 'Kolekcja "Background" z Figmy (src/scss/tokens/_background.scss).'
    },
    source: {
      code: backgroundContent
    }
  }
};

export const Text = () => textContent;
Text.storyName = 'Text';
Text.parameters = {
  docs: {
    description: {
      story: 'Kolekcja "Text" z Figmy.'
    },
    source: {
      code: textContent
    }
  }
};

export const Foreground = () => foregroundContent;
Foreground.storyName = 'Foreground';
Foreground.parameters = {
  docs: {
    description: {
      story: 'Kolekcja "Foreground" z Figmy.'
    },
    source: {
      code: foregroundContent
    }
  }
};

export const Border = () => borderContent;
Border.storyName = 'Border';
Border.parameters = {
  docs: {
    description: {
      story: 'Kolekcja "Border" z Figmy (src/scss/tokens/_border.scss).'
    },
    source: {
      code: borderContent
    }
  }
};
