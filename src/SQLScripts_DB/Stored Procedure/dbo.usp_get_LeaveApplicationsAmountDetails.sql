USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_LeaveApplicationsAmountDetails]    Script Date: 28/01/2022 14:33:30 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_get_LeaveApplicationsAmountDetails]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[usp_get_LeaveApplicationsAmountDetails]

	-- Add the parameters for the stored procedure here
	@leaveapplicationid int,
	@leaveid int,
	@startingdate varchar(100),
	@endingdate varchar(100)
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	SELECT SUM(rla.Amount)as ''Amount'',rla.LeaveTypeID,la.LeaveID from LeaveApplications la
inner Join ref_LeaveApplications_Amounts rla on rla.ApplicationID = la.ID
where la.ID != @leaveapplicationid AND LeaveID =@leaveid and la.dDate >= @startingdate and la.dDate<=@endingdate
Group by rla.LeaveTypeID,la.LeaveID
				
END
'
EXEC dbo.sp_executesql @strSQL

GO