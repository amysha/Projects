function [ce, frac_correct] = evaluate(targets, y)
%    Compute evaluation metrics.
%    Inputs:
%        targets : N x 1 vector of binary targets. Values should be either 0 or 1.
%        y       : N x 1 vector of probabilities.
%    Outputs:
%        ce           : (scalar) Cross entropy. CE(p, q) = E_p[-log q]. Here we
%                       want to compute CE(targets, y).
%        frac_correct : (scalar) Fraction of inputs classified correctly.

% TODO: Finish this function

ce = -sum(targets .* log(y)) - sum((1-targets) .* log(1-y));

class = zeros(length(y), 1);
for i = 1:length(y);
    if y(i) >= 0.5
        class(i) = 1;
    else
        class(i) = 0;
    end
end

frac_correct = sum(class==targets)/length(class);

end
