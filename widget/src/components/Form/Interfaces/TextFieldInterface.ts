import { FormElementInterface } from './FormElementInterface';

export interface TextFieldInterface extends FormElementInterface {
  placeholder: string;
  rows: number;
}
