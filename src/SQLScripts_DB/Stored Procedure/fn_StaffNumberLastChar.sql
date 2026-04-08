Use [Allocate7]

GO

CREATE OR ALTER FUNCTION dbo.fn_StaffNumberLastChar ( @Emp  int)
RETURNS CHAR(1) 
AS

BEGIN

Declare @total INT
Declare @chksum INT
Declare @lastchar char
Declare @empnumber nvarchar(6)

SET @empnumber=CONVERT(nvarchar(6),@emp)

SET @total=(
			(substring(@empnumber,1,1)	*7)+
			(substring(@empnumber,2,1)	*5)+
			(substring(@empnumber,3,1)	*3)+
			(substring(@empnumber,4,1)	*1)+
			(substring(@empnumber,5,1)	*11)+
			(substring(@empnumber,6,1)	*13)
		   )

SET @chksum=(@total % 17) + 1
 
SET @lastchar=  case
         when @chksum = 1			  then 'A'
         when @chksum = 2             then 'B'
         when @chksum = 3             then 'D'
         when @chksum = 4             then 'E'
         when @chksum = 5             then 'F'
         when @chksum = 6             then 'H'
         when @chksum = 7             then 'J'
         when @chksum = 8             then 'K'
         when @chksum = 9             then 'L'
         when @chksum = 10            then 'N'
         when @chksum = 11            then 'P'
         when @chksum = 12            then 'R'
         when @chksum = 13            then 'S'
         when @chksum = 14            then 'T'
         when @chksum = 15            then 'W'
         when @chksum = 16            then 'X'
         when @chksum = 17            then 'Y'
         Else			              ''
    End 

return @lastchar

END