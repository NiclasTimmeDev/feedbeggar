import { FormElementInterface } from './FormElementInterface';

export interface CheckboxInterface extends FormElementInterface {
  label: string;
  isChecked: boolean;
}
