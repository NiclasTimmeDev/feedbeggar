import html2canvas from 'html2canvas';

export class ScreenshotHandler {
  /**
   * Take a screenshot of the page using the html2canvas library.
   * @returns
   */
  static takeScreenshot = async (): Promise<string> => {
    const canvas = await html2canvas(document.body);
    console.log(canvas.toDataURL('image/jpeg', 0.9));
    return canvas.toDataURL('image/jpeg', 0.9);
  };

  /**
   * Enable the user to paint on the screen.
   */
  static painOnScreen = () => {
    // Create canvas element and append it to document body
    let canvas = document.createElement('canvas');
    canvas.classList.add('feedback-form--painter-canvas');
    document.body.appendChild(canvas);

    // Some hofixes.
    document.body.style.margin = '0';
    canvas.style.position = 'fixed';

    // Get canvas 2D context and set it to correct size.
    var ctx = canvas.getContext('2d')!;
    resize();

    // Last known position.
    var pos = { x: 0, y: 0 };

    // new position from mouse event
    function setPosition(e: MouseEvent) {
      pos.x = e.clientX;
      pos.y = e.clientY;
    }

    // resize canvas
    function resize() {
      ctx.canvas.width = window.innerWidth;
      ctx.canvas.height = window.innerHeight;
    }

    function draw(e: MouseEvent) {
      // mouse left button must be pressed
      if (e.buttons !== 1) return;

      ctx.beginPath(); // begin

      ctx.lineWidth = 5;
      ctx.lineCap = 'round';
      ctx.strokeStyle = '#c0392b';

      ctx.moveTo(pos.x, pos.y); // from
      setPosition(e);
      ctx.lineTo(pos.x, pos.y); // to

      ctx.stroke(); // draw it!
    }
    // Add all the necessary event listenders.
    window.addEventListener('resize', resize);
    document.addEventListener('mousemove', draw);
    document.addEventListener('mousedown', setPosition);
    document.addEventListener('mouseenter', setPosition);
  };

  static removePaintOnScreenElement = (): boolean => {
    const canvas = document.querySelector('.feedback-form--painter-canvas')!;

    if (!canvas) {
      return false;
    }
    canvas.remove();
    return true;
  };
}
