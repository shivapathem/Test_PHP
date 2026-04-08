USE [ALLOCATE7]

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_get_userFavouriteGroup]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[usp_get_userFavouriteGroup]
@netlogin Varchar(50)
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
    
	 SELECT u1.ID,u1.description as GroupDescription,u5.TeamID 
	 FROM  user_favourites  u1 join user_favourites_staff_link u2 on u1.ID = u2.userfavouriteid 
				 INNER JOIN     StaffDetails u3 ON u2.staffnumber = u3.StaffNumber
				 INNER JOIN     ScheduledPeople u4 ON u4.StaffDetailsID = u3.StaffID
                 INNER JOIN     ScheduledPersonTeam_LINK u5 ON u5.ScheduledPersonID = u4.ScheduledPersonID
				  WHERE    u1.Login = @netlogin and  u5.IsHomeTeam = 1 and u5.scheduledType = 1  
				  AND isnull(u5.EndDate,''9999-01-01'') >= getdate()  
				  
	
END
'

EXEC dbo.sp_executesql @strSQL

GO