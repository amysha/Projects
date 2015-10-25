function [f, df, y] = logistic_pen(weights, data, targets, hyperparameters)
% Calculate log likelihood and derivatives with respect to weights.
%
% Note: N is the number of examples and 
%       M is the number of features per example.
%
% Inputs:
% 	weights:    (M+1) x 1 vector of weights, where the last element
%               corresponds to bias (intercepts).
% 	data:       N x M data matrix where each row corresponds 
%               to one data point.
%   targets:    N x 1 vector of targets class probabilities.
%   hyperparameters: The hyperparameter structure
%
% Outputs:
%	f:             The scalar error value.
%	df:            (M+1) x 1 vector of derivatives of error w.r.t. weights.
%   y:             N x 1 vector of probabilities. This is the output of the classifier.
%

%TODO: finish this function

y = logistic_predict(weights, data);

lamda = hyperparameters.weight_regularization;
w = weights(1:end-1);
b = weights(end);

e = -sum(targets .* log(y))-sum((1-targets) .* log(1-y));
f = e + (lamda/2) * sum(w.^2) + (lamda/2) * (b.^2); 

dw = data' * (y - targets) + lamda .* w;
db = sum(y - targets) + lamda .* b;
df = [dw; db];

end
