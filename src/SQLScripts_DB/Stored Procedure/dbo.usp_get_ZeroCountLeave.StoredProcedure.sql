USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_ZeroCountLeave]    Script Date: 10/03/2022 14:33:30 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_get_ZeroCountLeave]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[usp_get_ZeroCountLeave]
@StartYear varchar(100),
@NextYear varchar(100),
@NetLogin varchar(100)
AS
BEGIN


SET NOCOUNT ON;

SELECT        count(LeaveApplications.ID) AS CountLeave
               FROM          LeaveApplications (nolock)
               INNER JOIN    leave_types (nolock) ON LeaveApplications.LeaveTypesID = leave_types.ID 
               INNER JOIN    LeaveRequestGroups (nolock) ON leave_types.GroupID = LeaveRequestGroups.ID
               WHERE         (LeaveApplications.Deleted = 0) AND (LeaveApplications.dDate >= CONVERT(DATETIME, @StartYear+''-04-01 00:00:00'', 102)) 
               AND           (LeaveApplications.dDate <= CONVERT(DATETIME,@NextYear+''-03-31 00:00:00'', 102))
			    AND           (LeaveApplications.CountLeave = 0)
               AND      (LeaveApplications.Login = @NetLogin)


END
'
EXEC dbo.sp_executesql @strSQL

GO
