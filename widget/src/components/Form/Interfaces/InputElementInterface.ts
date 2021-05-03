import { FormElementInterface } from './FormElementInterface';

export interface InpuElementInterface extends FormElementInterface {
  type: string;
  placeholder: string;
}
