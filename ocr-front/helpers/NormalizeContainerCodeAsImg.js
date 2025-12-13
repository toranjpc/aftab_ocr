export default function (v, color = '#2957a4', v2 = '', diffColor = '#fff', nocontiner = 0) {
  try {
    if (v || v2) {
      const regex = /^([A-Za-z]{0,4})(\d{0,6})(\d{0,1})(.*)$/
      // const split = v.match(regex)
      const split2 = v2 ? v2.match(regex) : null

      let letters = "";
      let numbers = "";
      let singleDigit = "";
      let remaining = "";

      if (v) {
        const split = v.split("_")
        letters = (split[0] || "").toUpperCase();
        numbers = split[1] || "";
        singleDigit = split[2] || "";
        remaining = split[3] || "";
        // letters = letters.padEnd(4, '?').slice(0, 4);

        // numbers = numbers.padEnd(6, '?').slice(0, 6);
        // singleDigit = singleDigit || '?';

        letters = letters.padEnd(4, '*').slice(0, 4);
        numbers = numbers.padEnd(6, '*').slice(0, 6);
        singleDigit = singleDigit || '*';

      } else {
        color = '#2aa2db'
        letters = split2 ? (split2[1] || "").toUpperCase() : ""
        numbers = split2 ? (split2[2] || "") : ""
        singleDigit = split2 ? (split2[3] || "") : ""
      }

      // Prepare comparison strings
      const letters2 = split2 ? (split2[1] || "").toUpperCase() : ""
      const numbers2 = split2 ? (split2[2] || "") : ""
      const singleDigit2 = split2 ? (split2[3] || "") : ""

      // Highlight differences in letters
      // letters = letters.padEnd(4, '?').slice(0, 4);

      // numbers = numbers.padEnd(6, '?').slice(0, 6);
      // singleDigit = singleDigit || '?';

      if (v && v2 && v2 !== '') {
        let highlightedLetters = ""
        let highlightedNumbers = ""

        for (let i = 0; i < 4; i++) {
          // const char = letters[i] || '?'
          // const char2 = letters2[i] || '?'

          const char = letters[i] || '*'
          const char2 = letters2[i] || '*'
          if (char !== char2)
            highlightedLetters += `<span style="color:${diffColor}">${char2}</span>`
          else
            highlightedLetters += char
        }

        for (let j = 0; j < 6; j++) {
          // const charj = numbers[j] || '?'
          // const charj2 = numbers2[j] || '?'

          const charj = numbers[j] || '*'
          const charj2 = numbers2[j] || '*'
          if (charj !== charj2)
            highlightedNumbers += `<span style="color:${diffColor}">${charj}</span>`
          else
            highlightedNumbers += charj2
          // console.log(numbers[j], numbers, highlightedNumbers)
        }

        letters = highlightedLetters
        numbers = highlightedNumbers

        // const isSingleDigitDiff = singleDigit !== (singleDigit2 || '?')
        const isSingleDigitDiff = singleDigit !== (singleDigit2 || '*')
        singleDigit = isSingleDigitDiff
          ? `<span style="color:${diffColor}">${singleDigit2}</span>`
          : singleDigit
      }

      return (
        `<div style="border-radius: 4px; min-width: 138px;min-height: 53px;background-size: contain; display:flex;flex-direction: row-reverse;width: fit-content;text-align: left; font-family: sans-serif !important; font-weight: bold;padding: 2px; padding-top: 8px; padding-left: 11px; padding-bottom: 0px; color: #ffcc29; background: ${color};"><span style="direction:ltr">` +
        letters +
        '  ' +
        `</span>
        <div style="margin: 0px 6px 0 11px;display:flex;flex-direction:column;width: fit-content;text-align: left;"><span style="direction:ltr">` +
        numbers +
        `</span><span>` +
        remaining +
        `</span></div>` +
        `<span style="border: 1px solid;padding: 0 4px;height: fit-content; margin-right: 10px;">` +
        singleDigit +
        `</span>` +
        `</div>`
      )
    }
    return ''
  } catch (e) { }
}
