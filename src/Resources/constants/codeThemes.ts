export type CodeTheme = 'github-dark' | 'github-light' | 'nord' | 'one-dark-pro' | 'dracula' | 'monokai' | 'tokyo-night' | 'catppuccin-frappe';

export const CODE_THEMES: Array<{ value: CodeTheme; label: string; description: string }> = [
  { value: 'github-dark', label: 'GitHub Dark', description: 'GitHub\'s default dark theme' },
  { value: 'github-light', label: 'GitHub Light', description: 'GitHub\'s default light theme' },
  { value: 'nord', label: 'Nord', description: 'Arctic, north-bluish color palette' },
  { value: 'one-dark-pro', label: 'One Dark Pro', description: 'Atom\'s One Dark theme' },
  { value: 'dracula', label: 'Dracula', description: 'Dark theme with purple accent' },
  { value: 'monokai', label: 'Monokai', description: 'Classic color scheme inspired by Monokai Pro' },
  { value: 'tokyo-night', label: 'Tokyo Night', description: 'Clean, dark theme based on Tokyo Night' },
  { value: 'catppuccin-frappe', label: 'Catppuccin Frappé', description: 'Soothing pastel theme for the high-spirited!' }
];

export const getThemeByValue = (value: string): { value: CodeTheme; label: string; description: string } | undefined => {
  return CODE_THEMES.find(theme => theme.value === value);
};